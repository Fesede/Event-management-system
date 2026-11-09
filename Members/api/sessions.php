<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('GET');
$account = attendance_api_require_holder();

$stmt = $conn->prepare(
    "SELECT id, title, session_start, checkin_opens_at, checkin_closes_at, location, status
     FROM attendance_sessions
     WHERE holder_user_id = ? AND status = 'open'
     ORDER BY session_start DESC
     LIMIT 100"
);
if (!$stmt) {
    error_log('Attendance app sessions query could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not load assigned sessions.']);
}
$user_id = (int)$account['user_id'];
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
    error_log('Attendance app sessions query failed: ' . $stmt->error);
    $stmt->close();
    attendance_api_json(500, ['error' => 'Could not load assigned sessions.']);
}
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($sessions as &$session) {
    $session['id'] = (int)$session['id'];
    $session['checkin_open'] = attendance_is_checkin_open($session);
}
unset($session);

attendance_api_json(200, ['sessions' => $sessions]);
