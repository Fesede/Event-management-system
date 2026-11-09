<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('POST');
$account = attendance_api_require_holder();

$request = attendance_api_read_json();
$session_id = filter_var($request['session_id'] ?? null, FILTER_VALIDATE_INT);
$qr_token = $request['qr_token'] ?? null;
if ($session_id === false || $session_id < 1 || !is_string($qr_token)) {
    attendance_api_json(400, ['error' => 'The session or scanned QR code is invalid.']);
}
$qr_token = strtolower(trim($qr_token));
if (!preg_match('/^[a-f0-9]{32}$/', $qr_token)) {
    attendance_api_json(400, ['error' => 'This is not a valid St. Raguel member QR code.']);
}

$session = attendance_get_session($conn, $session_id);
if (!$session || !attendance_can_record($session)) {
    attendance_api_json(403, ['error' => 'This session is not open or is not assigned to your account.']);
}
if (!attendance_is_checkin_open($session)) {
    attendance_api_json(409, ['error' => 'This session is outside its check-in window.']);
}

$member_stmt = $conn->prepare(
    'SELECT m.id, m.full_Name
     FROM members m
     JOIN attendance_session_roster r ON r.member_id = m.id
     WHERE r.session_id = ? AND m.qr_token = ?
     LIMIT 1'
);
if (!$member_stmt) {
    error_log('Attendance app QR lookup could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not verify the member QR code.']);
}
$member_stmt->bind_param('is', $session_id, $qr_token);
if (!$member_stmt->execute()) {
    error_log('Attendance app QR lookup failed: ' . $member_stmt->error);
    $member_stmt->close();
    attendance_api_json(500, ['error' => 'Could not verify the member QR code.']);
}
$member = $member_stmt->get_result()->fetch_assoc();
$member_stmt->close();
if (!$member) {
    attendance_api_json(404, ['error' => 'This member is not on the selected session roster.']);
}

try {
    attendance_save_record($conn, $session, (int)$member['id'], 'present', 'qr');
} catch (InvalidArgumentException $exception) {
    attendance_api_json(409, ['error' => $exception->getMessage()]);
} catch (RuntimeException $exception) {
    error_log('Attendance app check-in failed: ' . $exception->getMessage());
    attendance_api_json(500, ['error' => 'Could not save this check-in. Try again.']);
}

attendance_api_json(200, [
    'message' => 'Checked in successfully.',
    'member' => [
        'id' => (int)$member['id'],
        'name' => $member['full_Name'],
    ],
]);
