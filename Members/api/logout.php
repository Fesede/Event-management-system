<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('POST');
$account = attendance_api_require_holder();

$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([a-f0-9]{64})$/i', $authorization, $matches)) {
    attendance_api_json(401, ['error' => 'Sign in as an Attendance Holder to continue.']);
}
$token_hash = hash('sha256', strtolower($matches[1]));
$stmt = $conn->prepare(
    'UPDATE attendance_api_tokens SET revoked_at = CURRENT_TIMESTAMP
     WHERE token_hash = ? AND user_id = ? AND revoked_at IS NULL'
);
if (!$stmt) {
    error_log('Attendance app logout could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not sign out.']);
}
$user_id = (int)$account['user_id'];
$stmt->bind_param('si', $token_hash, $user_id);
if (!$stmt->execute()) {
    error_log('Attendance app logout failed: ' . $stmt->error);
    $stmt->close();
    attendance_api_json(500, ['error' => 'Could not sign out.']);
}
$stmt->close();
attendance_api_json(200, ['message' => 'Signed out.']);
