<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('POST');
attendance_api_require_https();

$request = attendance_api_read_json();
$email = $request['email'] ?? null;
$password = $request['password'] ?? null;
if (!is_string($email) || !is_string($password) || trim($email) === '' || $password === '') {
    attendance_api_json(400, ['error' => 'Enter your Attendance Holder email and password.']);
}

$stmt = $conn->prepare(
    "SELECT id, first_name, last_name, password
     FROM users
     WHERE email = ? AND role = 'Attendance Holder' AND status = 'active'
     LIMIT 1"
);
if (!$stmt) {
    error_log('Attendance app login could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not sign in right now.']);
}
$stmt->bind_param('s', $email);
if (!$stmt->execute()) {
    error_log('Attendance app login lookup failed: ' . $stmt->error);
    $stmt->close();
    attendance_api_json(500, ['error' => 'Could not sign in right now.']);
}
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account || !password_verify($password, $account['password'])) {
    attendance_api_json(401, ['error' => 'The email or password is incorrect, or this account is not an active Attendance Holder.']);
}

try {
    $token = bin2hex(random_bytes(32));
} catch (Random\RandomException $exception) {
    error_log('Attendance app token generation failed: ' . $exception->getMessage());
    attendance_api_json(500, ['error' => 'Could not sign in right now.']);
}
$token_hash = hash('sha256', $token);
$save = $conn->prepare(
    'INSERT INTO attendance_api_tokens (user_id, token_hash, expires_at)
     VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY))'
);
if (!$save) {
    error_log('Attendance app token insert could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not complete app sign-in.']);
}
$user_id = (int)$account['id'];
$save->bind_param('is', $user_id, $token_hash);
if (!$save->execute()) {
    error_log('Attendance app token insert failed: ' . $save->error);
    $save->close();
    attendance_api_json(500, ['error' => 'Could not complete app sign-in.']);
}
$save->close();

attendance_api_json(200, [
    'token' => $token,
    'expires_in' => 2592000,
    'user' => [
        'name' => trim($account['first_name'] . ' ' . $account['last_name']),
    ],
]);
