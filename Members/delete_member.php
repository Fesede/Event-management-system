<?php
require_once 'includes/attendance.php';
include 'includes/connect.php';

attendance_require_roles(['super admin', 'members manager']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Use POST to deactivate a member.');
}
attendance_validate_csrf();

$member_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($member_id === false || $member_id < 1) {
    http_response_code(400);
    exit('A valid member ID is required.');
}

$stmt = $conn->prepare("UPDATE members SET membership_status = 'inactive' WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    exit('Could not prepare the member deactivation: ' . attendance_escape($conn->error));
}
$stmt->bind_param('i', $member_id);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Could not deactivate the member: ' . attendance_escape($stmt->error));
}

header('Location: members.php?status=deactivated');
exit();
