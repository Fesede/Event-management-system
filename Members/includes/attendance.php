<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connect.php';

$attendance_role = strtolower(trim($_SESSION['user_role'] ?? ''));
$attendance_user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

function attendance_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function attendance_post_string($name)
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}

function attendance_parse_local_datetime($value)
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
        return null;
    }

    $date = DateTime::createFromFormat('!Y-m-d\TH:i', $value);
    $errors = DateTime::getLastErrors();
    if ($date === false
        || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        || $date->format('Y-m-d\TH:i') !== $value) {
        return null;
    }

    return $date;
}

function attendance_require_roles(array $roles)
{
    global $attendance_role, $attendance_user_id, $conn;

    if ($attendance_role === '') {
        header('Location: ../login.php');
        exit();
    }

    if (!in_array($attendance_role, $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }

    if (($_SESSION['user_id'] ?? null) === 'fixed_members' && $attendance_role === 'members manager') {
        return;
    }
    if ($attendance_user_id === false) {
        http_response_code(403);
        exit('Your account is not linked to a valid database user.');
    }

    $stmt = $conn->prepare('SELECT role, status FROM users WHERE id = ?');
    if (!$stmt) {
        http_response_code(500);
        exit('Could not verify your account: ' . attendance_escape($conn->error));
    }
    $stmt->bind_param('i', $attendance_user_id);
    if (!$stmt->execute()) {
        http_response_code(500);
        exit('Could not verify your account: ' . attendance_escape($stmt->error));
    }
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$account || $account['status'] !== 'active' || strtolower(trim($account['role'])) !== $attendance_role) {
        $_SESSION = [];
        session_destroy();
        header('Location: ../login.php?error=unauthorized');
        exit();
    }
}

function attendance_csrf_token()
{
    if (empty($_SESSION['attendance_csrf'])) {
        $_SESSION['attendance_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['attendance_csrf'];
}

function attendance_validate_csrf()
{
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['attendance_csrf'] ?? '';

    if (!is_string($submitted) || !is_string($expected) || $expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(400);
        exit('Your session token is invalid or expired. Refresh the page and try again.');
    }
}

function attendance_log(mysqli $conn, $session_id, $member_id, $action, $before, $after, $reason)
{
    global $attendance_user_id;

    $stmt = $conn->prepare(
        'INSERT INTO attendance_audit_log (session_id, member_id, actor_user_id, action, before_value, after_value, reason)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new RuntimeException('Could not prepare the attendance audit record: ' . $conn->error);
    }

    $before_value = $before === null ? null : json_encode($before);
    $after_value = $after === null ? null : json_encode($after);
    $actor_user_id = $attendance_user_id === false ? null : $attendance_user_id;
    $stmt->bind_param('iiissss', $session_id, $member_id, $actor_user_id, $action, $before_value, $after_value, $reason);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Could not save the attendance audit record: ' . $error);
    }
    $stmt->close();
}

function attendance_get_session(mysqli $conn, $session_id)
{
    $stmt = $conn->prepare(
        'SELECT s.*, u.first_name AS holder_first_name, u.last_name AS holder_last_name
         FROM attendance_sessions s
         LEFT JOIN users u ON u.id = s.holder_user_id
         WHERE s.id = ?'
    );
    if (!$stmt) {
        throw new RuntimeException('Could not prepare the attendance session lookup: ' . $conn->error);
    }

    $stmt->bind_param('i', $session_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Could not load the attendance session: ' . $error);
    }

    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $session ?: null;
}

function attendance_can_view_session(array $session)
{
    global $attendance_role, $attendance_user_id;

    if (in_array($attendance_role, ['super admin', 'members manager', 'attendance controller'], true)) {
        return true;
    }

    return $attendance_role === 'attendance holder'
        && $attendance_user_id !== false
        && (int)$session['holder_user_id'] === $attendance_user_id;
}

function attendance_can_record(array $session)
{
    global $attendance_role, $attendance_user_id;

    return $attendance_role === 'attendance holder'
        && $attendance_user_id !== false
        && (int)$session['holder_user_id'] === $attendance_user_id
        && $session['status'] === 'open';
}

function attendance_is_checkin_open(array $session)
{
    $opens_at = strtotime($session['checkin_opens_at'] ?? '');
    $closes_at = strtotime($session['checkin_closes_at'] ?? '');
    $now = time();

    return $opens_at !== false && $closes_at !== false && $now >= $opens_at && $now <= $closes_at;
}

function attendance_can_submit(array $session)
{
    if (!attendance_can_record($session)) {
        return false;
    }

    $closes_at = strtotime($session['checkin_closes_at'] ?? '');
    return $closes_at !== false && time() > $closes_at;
}

function attendance_change_session_status(mysqli $conn, array $session, $next_status, $action, $reason = null)
{
    global $attendance_role, $attendance_user_id;

    $current_status = $session['status'];
    $allowed_transitions = [
        'open' => ['submitted' => ['attendance holder' => 'session_submitted']],
        'submitted' => ['approved' => ['attendance controller' => 'session_approved', 'super admin' => 'session_approved']],
        'approved' => ['submitted' => ['attendance controller' => 'session_reopened', 'super admin' => 'session_reopened']],
    ];
    if (!isset($allowed_transitions[$current_status][$next_status][$attendance_role])
        || $allowed_transitions[$current_status][$next_status][$attendance_role] !== $action
        || $attendance_user_id === false) {
        throw new InvalidArgumentException('You are not allowed to perform this session transition.');
    }

    $assignments = "status = '$next_status'";
    if ($next_status === 'submitted' && $current_status === 'open') {
        $assignments .= ', submitted_at = CURRENT_TIMESTAMP';
    } elseif ($next_status === 'approved') {
        $assignments .= ', approved_by = ?, approved_at = CURRENT_TIMESTAMP';
    } elseif ($current_status === 'approved') {
        $assignments .= ', approved_by = NULL, approved_at = NULL';
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE attendance_sessions SET $assignments WHERE id = ? AND status = ?");
        if (!$stmt) {
            throw new RuntimeException('Could not prepare the session status update: ' . $conn->error);
        }
        if ($next_status === 'approved') {
            $stmt->bind_param('iis', $attendance_user_id, $session['id'], $current_status);
        } else {
            $stmt->bind_param('is', $session['id'], $current_status);
        }
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not update the attendance session: ' . $stmt->error);
        }
        if ($stmt->affected_rows !== 1) {
            $stmt->close();
            throw new RuntimeException('The session status changed. Refresh and try again.');
        }
        $stmt->close();

        attendance_log(
            $conn,
            $session['id'],
            null,
            $action,
            ['status' => $current_status],
            ['status' => $next_status],
            $reason
        );
        $conn->commit();
    } catch (RuntimeException | InvalidArgumentException $exception) {
        $conn->rollback();
        throw $exception;
    }
}

function attendance_save_record(mysqli $conn, array $session, $member_id, $status, $method, $reason = null)
{
    global $attendance_role, $attendance_user_id;

    $conn->begin_transaction();
    try {
        if ($attendance_user_id === false) {
            throw new InvalidArgumentException('Your account must be linked to a database user to record attendance.');
        }
        $session_stmt = $conn->prepare(
            'SELECT status, holder_user_id, checkin_opens_at, checkin_closes_at
             FROM attendance_sessions WHERE id = ? FOR UPDATE'
        );
        if (!$session_stmt) {
            throw new RuntimeException('Could not prepare the session status check: ' . $conn->error);
        }
        $session_stmt->bind_param('i', $session['id']);
        if (!$session_stmt->execute()) {
            throw new RuntimeException('Could not verify the session status: ' . $session_stmt->error);
        }
        $current_session = $session_stmt->get_result()->fetch_assoc();
        $session_stmt->close();
        if (!$current_session) {
            throw new InvalidArgumentException('Attendance session not found.');
        }
        $can_correct = $method === 'correction'
            && in_array($attendance_role, ['super admin', 'attendance controller'], true)
            && $current_session['status'] === 'submitted';
        $can_record = in_array($method, ['manual', 'qr'], true)
            && $attendance_role === 'attendance holder'
            && (int)$current_session['holder_user_id'] === $attendance_user_id
            && $current_session['status'] === 'open';
        $checkin_open = attendance_is_checkin_open($current_session);
        if (!$can_correct && (!$can_record || !$checkin_open)) {
            throw new InvalidArgumentException('The session is locked, outside its check-in window, or you do not have permission to change attendance.');
        }

        $roster_stmt = $conn->prepare(
            'SELECT 1 FROM attendance_session_roster WHERE session_id = ? AND member_id = ? FOR UPDATE'
        );
        if (!$roster_stmt) {
            throw new RuntimeException('Could not prepare the session roster check: ' . $conn->error);
        }
        $roster_stmt->bind_param('ii', $session['id'], $member_id);
        if (!$roster_stmt->execute()) {
            throw new RuntimeException('Could not verify the member roster: ' . $roster_stmt->error);
        }
        $is_rostered = $roster_stmt->get_result()->num_rows === 1;
        $roster_stmt->close();
        if (!$is_rostered) {
            throw new InvalidArgumentException('This member is not on the session roster.');
        }

        $before_stmt = $conn->prepare(
            'SELECT status, method, recorded_by, reason FROM attendance_records WHERE session_id = ? AND member_id = ? FOR UPDATE'
        );
        if (!$before_stmt) {
            throw new RuntimeException('Could not prepare the existing attendance lookup: ' . $conn->error);
        }
        $before_stmt->bind_param('ii', $session['id'], $member_id);
        if (!$before_stmt->execute()) {
            throw new RuntimeException('Could not load the existing attendance record: ' . $before_stmt->error);
        }
        $before = $before_stmt->get_result()->fetch_assoc() ?: null;
        $before_stmt->close();
        if ($before !== null && $method !== 'correction') {
            throw new InvalidArgumentException(
                'Attendance is already recorded as ' . $before['status'] . ' (' . $before['method'] . ').'
            );
        }

        $stmt = $conn->prepare(
            "INSERT INTO attendance_records (session_id, member_id, status, method, recorded_by, recorded_at, reason)
             VALUES (?, ?, ?, ?, ?, IF(? = 'absent', NULL, CURRENT_TIMESTAMP), ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), method = VALUES(method),
               recorded_by = VALUES(recorded_by), recorded_at = VALUES(recorded_at), reason = VALUES(reason)"
        );
        if (!$stmt) {
            throw new RuntimeException('Could not prepare the attendance record: ' . $conn->error);
        }
        $stmt->bind_param(
            'iississ',
            $session['id'],
            $member_id,
            $status,
            $method,
            $attendance_user_id,
            $status,
            $reason
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not save the attendance record: ' . $stmt->error);
        }
        $stmt->close();

        attendance_log(
            $conn,
            $session['id'],
            $member_id,
            $method === 'correction' ? 'attendance_corrected' : 'attendance_recorded',
            $before,
            ['status' => $status, 'method' => $method, 'recorded_by' => $attendance_user_id],
            $reason
        );
        $conn->commit();
    } catch (RuntimeException | InvalidArgumentException $exception) {
        $conn->rollback();
        throw $exception;
    }
}
