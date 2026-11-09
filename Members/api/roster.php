<?php
require_once __DIR__ . '/common.php';
attendance_api_require_method('GET');
$account = attendance_api_require_holder();

$session_id = filter_var($_GET['session_id'] ?? null, FILTER_VALIDATE_INT);
if ($session_id === false || $session_id < 1) {
    attendance_api_json(400, ['error' => 'Select a valid attendance session.']);
}

$session_stmt = $conn->prepare(
    "SELECT id, title
     FROM attendance_sessions
     WHERE id = ? AND holder_user_id = ? AND status = 'open'
     LIMIT 1"
);
if (!$session_stmt) {
    error_log('Attendance app roster session lookup could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not load the session roster.']);
}
$user_id = (int)$account['user_id'];
$session_stmt->bind_param('ii', $session_id, $user_id);
if (!$session_stmt->execute()) {
    error_log('Attendance app roster session lookup failed: ' . $session_stmt->error);
    $session_stmt->close();
    attendance_api_json(500, ['error' => 'Could not load the session roster.']);
}
$session = $session_stmt->get_result()->fetch_assoc();
$session_stmt->close();
if (!$session) {
    attendance_api_json(403, ['error' => 'This session is not open or is not assigned to your account.']);
}

$roster_stmt = $conn->prepare(
    "SELECT m.id, m.full_Name AS name, m.qr_token,
            COALESCE(ar.status, 'absent') AS attendance_status,
            ar.method AS attendance_method, ar.recorded_at
     FROM attendance_session_roster r
     JOIN members m ON m.id = r.member_id
     LEFT JOIN attendance_records ar ON ar.session_id = r.session_id AND ar.member_id = r.member_id
     WHERE r.session_id = ?
     ORDER BY m.full_Name, m.id"
);
if (!$roster_stmt) {
    error_log('Attendance app roster query could not be prepared: ' . $conn->error);
    attendance_api_json(500, ['error' => 'Could not load the session roster.']);
}
$roster_stmt->bind_param('i', $session_id);
if (!$roster_stmt->execute()) {
    error_log('Attendance app roster query failed: ' . $roster_stmt->error);
    $roster_stmt->close();
    attendance_api_json(500, ['error' => 'Could not load the session roster.']);
}
$members = $roster_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$roster_stmt->close();

$counts = ['roster_count' => count($members), 'present_count' => 0, 'excused_count' => 0, 'absent_count' => 0];
foreach ($members as &$member) {
    $member['id'] = (int)$member['id'];
    if ($member['attendance_status'] === 'present') {
        $counts['present_count']++;
    } elseif ($member['attendance_status'] === 'excused') {
        $counts['excused_count']++;
    }
}
unset($member);
$counts['absent_count'] = max(0, $counts['roster_count'] - $counts['present_count'] - $counts['excused_count']);

attendance_api_json(200, [
    'session' => ['id' => (int)$session['id'], 'title' => $session['title']],
    'counts' => $counts,
    'members' => $members,
]);
