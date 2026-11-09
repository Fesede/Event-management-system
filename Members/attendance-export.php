<?php
require_once 'includes/attendance.php';
include 'includes/connect.php';

attendance_require_roles(['super admin', 'members manager', 'attendance controller']);

$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$session_id || $session_id < 1) {
    http_response_code(400);
    exit('A valid attendance session is required.');
}

$session = attendance_get_session($conn, $session_id);
if (!$session) {
    http_response_code(404);
    exit('Attendance session not found.');
}

$stmt = $conn->prepare(
    "SELECT m.full_Name, m.Phone_no, COALESCE(ar.status, 'absent') AS attendance_status,
            COALESCE(ar.method, 'not recorded') AS attendance_method, ar.recorded_at, ar.reason
     FROM attendance_session_roster r
     JOIN members m ON m.id = r.member_id
     LEFT JOIN attendance_records ar ON ar.session_id = r.session_id AND ar.member_id = r.member_id
     WHERE r.session_id = ?
     ORDER BY m.full_Name, m.id"
);
if (!$stmt) {
    http_response_code(500);
    exit('Could not prepare the attendance report: ' . attendance_escape($conn->error));
}
$stmt->bind_param('i', $session_id);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Could not load the attendance report: ' . attendance_escape($stmt->error));
}
$rows = $stmt->get_result();

function attendance_csv_safe($value)
{
    $value = (string)($value ?? '');
    return preg_match('/^\s*[=+\-@]/', $value) ? "'" . $value : $value;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance-session-' . $session_id . '.csv"');
echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    exit('Could not create the CSV report.');
}
fputcsv($output, ['Session', attendance_csv_safe($session['title'])], ',', '"', '');
fputcsv($output, ['Date', attendance_csv_safe($session['session_start'])], ',', '"', '');
fputcsv($output, ['Check-in opens', attendance_csv_safe($session['checkin_opens_at'])], ',', '"', '');
fputcsv($output, ['Check-in closes', attendance_csv_safe($session['checkin_closes_at'])], ',', '"', '');
fputcsv($output, ['Location', attendance_csv_safe($session['location'])], ',', '"', '');
fputcsv($output, [], ',', '"', '');
fputcsv($output, ['Member', 'Phone', 'Status', 'Method', 'Recorded at', 'Correction reason'], ',', '"', '');
while ($row = $rows->fetch_assoc()) {
    $values = array_map('attendance_csv_safe', array_values($row));
    fputcsv($output, $values, ',', '"', '');
}
fclose($output);
$stmt->close();
