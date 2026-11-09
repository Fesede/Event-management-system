<?php
require_once 'includes/attendance.php';
include 'includes/connect.php';

header('Content-Type: application/json; charset=utf-8');

function attendance_scan_response($status_code, $message)
{
    http_response_code($status_code);
    echo json_encode(['message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    attendance_scan_response(405, 'Use POST to record a QR check-in.');
}

if ($attendance_role !== 'attendance holder') {
    attendance_scan_response(403, 'Only an Attendance Holder can record QR check-ins.');
}
attendance_require_roles(['attendance holder']);
attendance_validate_csrf();

$session_id = filter_var($_POST['session_id'] ?? null, FILTER_VALIDATE_INT);
$qr_token = strtolower(attendance_post_string('qr_token'));
if ($session_id === false || $session_id < 1 || !preg_match('/^[a-f0-9]{32}$/', $qr_token)) {
    attendance_scan_response(400, 'The scanned QR code or session is invalid.');
}

$session = attendance_get_session($conn, $session_id);
if (!$session || !attendance_can_record($session)) {
    attendance_scan_response(403, 'This session is not open or is not assigned to you.');
}
if (!attendance_is_checkin_open($session)) {
    attendance_scan_response(409, 'This session is outside its check-in window.');
}

$member_stmt = $conn->prepare(
    'SELECT m.id, m.full_Name
     FROM members m
     JOIN attendance_session_roster r ON r.member_id = m.id
     WHERE r.session_id = ? AND m.qr_token = ?
     LIMIT 1'
);
if (!$member_stmt) {
    attendance_scan_response(500, 'Could not prepare the QR lookup: ' . $conn->error);
}
$member_stmt->bind_param('is', $session_id, $qr_token);
if (!$member_stmt->execute()) {
    attendance_scan_response(500, 'Could not verify the QR code: ' . $member_stmt->error);
}
$member = $member_stmt->get_result()->fetch_assoc();
$member_stmt->close();

if (!$member) {
    attendance_scan_response(404, 'This QR code is not for an active member on this session roster.');
}

try {
    attendance_save_record($conn, $session, (int)$member['id'], 'present', 'qr');
} catch (InvalidArgumentException $exception) {
    attendance_scan_response(409, $exception->getMessage());
} catch (RuntimeException $exception) {
    attendance_scan_response(500, 'Could not save this QR check-in: ' . $exception->getMessage());
}

echo json_encode(['message' => 'Checked in ' . $member['full_Name'] . '.']);
