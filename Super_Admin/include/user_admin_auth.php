<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'] ?? '')) !== 'super admin') {
    header('Location: ../login.php?error=unauthorized');
    exit();
}

require_once __DIR__ . '/conn.php';
$account_stmt = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
if (!$account_stmt) {
    http_response_code(500);
    exit('Could not verify the Super Admin account: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}
$account_stmt->bind_param('i', $_SESSION['user_id']);
if (!$account_stmt->execute()) {
    http_response_code(500);
    exit('Could not verify the Super Admin account: ' . htmlspecialchars($account_stmt->error, ENT_QUOTES, 'UTF-8'));
}
$account = $account_stmt->get_result()->fetch_assoc();
$account_stmt->close();
if (!$account || $account['status'] !== 'active' || strtolower(trim($account['role'])) !== 'super admin') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php?error=unauthorized');
    exit();
}

function super_admin_user_csrf_token()
{
    if (empty($_SESSION['user_admin_csrf'])) {
        $_SESSION['user_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['user_admin_csrf'];
}

function super_admin_validate_user_csrf()
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['user_admin_csrf'] ?? '';

    if (!is_string($submitted) || !is_string($expected) || $expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(400);
        exit('Your session token is invalid or expired. Refresh the page and try again.');
    }
}
