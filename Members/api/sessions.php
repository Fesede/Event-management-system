<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('GET');
$account = attendance_api_require_holder();

$stmt = $conn->prepare(
    "SELECT s.id, s.title, s.session_start, s.checkin_opens_at, s.checkin_closes_at,
            s.location, s.status,
            COUNT(DISTINCT r.member_id) AS roster_count,
            COUNT(DISTINCT CASE WHEN ar.status = 'present' THEN ar.member_id END) AS present_count,
            COUNT(DISTINCT CASE WHEN ar.status = 'excused' THEN ar.member_id END) AS excused_count
     FROM attendance_sessions s
     LEFT JOIN attendance_session_roster r ON r.session_id = s.id
     LEFT JOIN attendance_records ar ON ar.session_id = r.session_id AND ar.member_id = r.member_id
     WHERE s.holder_user_id = ? AND s.status = 'open'
     GROUP BY s.id, s.title, s.session_start, s.checkin_opens_at, s.checkin_closes_at,
              s.location, s.status
     ORDER BY s.session_start DESC
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
    $session['roster_count'] = (int)$session['roster_count'];
    $session['present_count'] = (int)$session['present_count'];
    $session['excused_count'] = (int)$session['excused_count'];
    $session['checkin_open'] = attendance_is_checkin_open($session);
}
unset($session);

attendance_api_json(200, ['sessions' => $sessions]);
