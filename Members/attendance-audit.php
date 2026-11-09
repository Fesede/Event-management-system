<?php
$page_title = 'Attendance Audit Log';
$active_tab = 'attendance';
require_once 'includes/attendance.php';
include 'includes/connect.php';

attendance_require_roles(['super admin']);

$session_id = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
$session_id = $session_id && $session_id > 0 ? $session_id : null;
$audit_sql =
    'SELECT l.id, l.session_id, l.member_id, l.action, l.before_value, l.after_value, l.reason, l.created_at,
            u.first_name, u.last_name, s.title AS session_title, m.full_Name AS member_name
     FROM attendance_audit_log l
     LEFT JOIN users u ON u.id = l.actor_user_id
     LEFT JOIN attendance_sessions s ON s.id = l.session_id
     LEFT JOIN members m ON m.id = l.member_id';

if ($session_id !== null) {
    $stmt = $conn->prepare($audit_sql . ' WHERE l.session_id = ? ORDER BY l.created_at DESC LIMIT 200');
    if (!$stmt) {
        http_response_code(500);
        exit('Could not prepare the audit log: ' . attendance_escape($conn->error));
    }
    $stmt->bind_param('i', $session_id);
} else {
    $stmt = $conn->prepare($audit_sql . ' ORDER BY l.created_at DESC LIMIT 200');
    if (!$stmt) {
        http_response_code(500);
        exit('Could not prepare the audit log: ' . attendance_escape($conn->error));
    }
}
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Could not load the audit log: ' . attendance_escape($stmt->error));
}
$audit_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Attendance audit log</h2>
        <p class="text-muted mb-0">Recent attendance changes, approvals, QR reissues, and session reopen actions.</p>
    </div>
    <a class="btn btn-outline-secondary" href="attendance.php"><i class="fas fa-arrow-left me-2"></i>Attendance dashboard</a>
</div>
<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Time</th><th>Action</th><th>Session</th><th>Member</th><th>Actor</th><th>Reason / change</th></tr></thead>
            <tbody>
                <?php if (!$audit_rows): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No audit records found.</td></tr>
                <?php else: foreach ($audit_rows as $row): ?>
                    <tr>
                        <td class="ps-4 small"><?php echo attendance_escape($row['created_at']); ?></td>
                        <td><span class="badge text-bg-primary"><?php echo attendance_escape(str_replace('_', ' ', $row['action'])); ?></span></td>
                        <td><?php if ($row['session_id']): ?><a href="attendance-session.php?id=<?php echo (int)$row['session_id']; ?>"><?php echo attendance_escape($row['session_title'] ?? 'Session'); ?></a><?php else: ?>—<?php endif; ?></td>
                        <td><?php echo attendance_escape($row['member_name'] ?? '—'); ?></td>
                        <td><?php echo attendance_escape(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'System account'); ?></td>
                        <td>
                            <?php if ($row['reason']): ?><div><?php echo attendance_escape($row['reason']); ?></div><?php endif; ?>
                            <?php if ($row['before_value'] || $row['after_value']): ?>
                                <details><summary class="small text-muted">Change details</summary>
                                    <pre class="small mb-0"><?php echo attendance_escape(($row['before_value'] ?? '—') . ' → ' . ($row['after_value'] ?? '—')); ?></pre>
                                </details>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
</body>
</html>
