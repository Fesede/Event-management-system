<?php
require_once __DIR__ . '/../includes/attendance.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function attendance_api_require_schema()
{
    global $conn;

    $required_tables = [
        'attendance_api_tokens',
        'attendance_sessions',
        'attendance_session_roster',
        'attendance_records',
        'attendance_audit_log',
    ];
    $table_names = "'" . implode("', '", $required_tables) . "'";
    try {
        $result = $conn->query(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name IN ($table_names)"
        );
    } catch (mysqli_sql_exception $exception) {
        error_log('Attendance API schema check failed: ' . $exception->getMessage());
        attendance_api_json(500, ['error' => 'Could not verify the attendance app setup.']);
    }
    if (!$result) {
        error_log('Attendance API schema check failed: ' . $conn->error);
        attendance_api_json(500, ['error' => 'Could not verify the attendance app setup.']);
    }

    $existing_tables = array_column($result->fetch_all(MYSQLI_ASSOC), 'table_name');
    $missing_tables = array_values(array_diff($required_tables, $existing_tables));
    if ($missing_tables) {
        attendance_api_json(503, [
            'error' => 'Attendance app database setup is incomplete. Apply Members/database/attendance_migration.sql and Members/database/attendance_api_migration.sql to st_raguel.',
        ]);
    }
}

function attendance_api_json($status_code, array $payload)
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}

function attendance_api_require_method($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        attendance_api_json(405, ['error' => 'Use ' . $method . ' for this endpoint.']);
    }
}

function attendance_api_read_json()
{
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 16384) {
        attendance_api_json(400, ['error' => 'The request body is missing or too large.']);
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        attendance_api_json(400, ['error' => 'Send a valid JSON request body.']);
    }

    return $data;
}

function attendance_api_require_holder()
{
    global $conn, $attendance_role, $attendance_user_id;

    attendance_api_require_https();
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authorization === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $header_name => $header_value) {
            if (strtolower($header_name) === 'authorization') {
                $authorization = $header_value;
                break;
            }
        }
    }
    if (!is_string($authorization) || !preg_match('/^Bearer ([a-f0-9]{64})$/i', $authorization, $matches)) {
        attendance_api_json(401, ['error' => 'Sign in as an Attendance Holder to continue.']);
    }

    $token_hash = hash('sha256', strtolower($matches[1]));
    $stmt = $conn->prepare(
        "SELECT t.id AS token_id, u.id AS user_id, u.first_name, u.last_name
         FROM attendance_api_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token_hash = ? AND t.revoked_at IS NULL
           AND t.expires_at > CURRENT_TIMESTAMP
           AND u.status = 'active' AND u.role = 'Attendance Holder'
         LIMIT 1"
    );
    if (!$stmt) {
        error_log('Attendance API token lookup could not be prepared: ' . $conn->error);
        attendance_api_json(500, ['error' => 'Could not verify the app sign-in.']);
    }
    $stmt->bind_param('s', $token_hash);
    if (!$stmt->execute()) {
        error_log('Attendance API token lookup failed: ' . $stmt->error);
        $stmt->close();
        attendance_api_json(500, ['error' => 'Could not verify the app sign-in.']);
    }
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$account) {
        attendance_api_json(401, ['error' => 'The app sign-in expired or was revoked. Sign in again.']);
    }

    $touch = $conn->prepare('UPDATE attendance_api_tokens SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?');
    if (!$touch) {
        error_log('Attendance API token update could not be prepared: ' . $conn->error);
        attendance_api_json(500, ['error' => 'Could not verify the app sign-in.']);
    }
    $token_id = (int)$account['token_id'];
    $touch->bind_param('i', $token_id);
    if (!$touch->execute()) {
        error_log('Attendance API token update failed: ' . $touch->error);
        $touch->close();
        attendance_api_json(500, ['error' => 'Could not verify the app sign-in.']);
    }
    $touch->close();

    $attendance_role = 'attendance holder';
    $attendance_user_id = (int)$account['user_id'];
    return $account;
}

function attendance_api_require_https()
{
    $is_https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    if (!$is_https && (int)($_SERVER['SERVER_PORT'] ?? 0) !== 443) {
        attendance_api_json(426, ['error' => 'The attendance app API requires HTTPS.']);
    }
}

attendance_api_require_schema();
