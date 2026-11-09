<?php
$page_title = 'Attendance Session';
$active_tab = 'attendance';
require_once 'includes/attendance.php';
include 'includes/connect.php';

attendance_require_roles([
    'super admin',
    'members manager',
    'attendance controller',
    'attendance holder',
]);

$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$session_id || $session_id < 1) {
    http_response_code(400);
    exit('A valid attendance session is required.');
}

$session = attendance_get_session($conn, $session_id);
if (!$session || !attendance_can_view_session($session)) {
    http_response_code($session ? 403 : 404);
    exit($session ? 'You do not have permission to view this session.' : 'Attendance session not found.');
}

$error_msg = '';
$notice_msg = '';
$is_controller = in_array($attendance_role, ['super admin', 'attendance controller'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    attendance_validate_csrf();
    $action = attendance_post_string('action');

    if ($action === 'record') {
        $member_id = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);
        $status = attendance_post_string('status');
        $reason = attendance_post_string('reason');
        $is_holder_recording = attendance_can_record($session)
            && attendance_is_checkin_open($session)
            && in_array($status, ['present', 'excused'], true);
        $is_controller_correction = $is_controller
            && $session['status'] === 'submitted'
            && in_array($status, ['present', 'excused', 'absent'], true)
            && $reason !== ''
            && strlen($reason) <= 500;

        if ($member_id === false || $member_id < 1 || (!$is_holder_recording && !$is_controller_correction)) {
            $error_msg = 'You cannot make this attendance change. Check the session status and your permissions.';
        } else {
            try {
                attendance_save_record(
                    $conn,
                    $session,
                    $member_id,
                    $status,
                    $is_controller_correction ? 'correction' : 'manual',
                    $reason === '' ? null : $reason
                );
                header('Location: attendance-session.php?id=' . $session_id . '&status=recorded');
                exit();
            } catch (RuntimeException | InvalidArgumentException $exception) {
                $error_msg = $exception->getMessage();
            }
        }
    } elseif ($action === 'submit') {
        if (!attendance_can_submit($session)) {
            $error_msg = 'Only the assigned Attendance Holder can submit the session after its check-in window closes.';
        } else {
            try {
                attendance_change_session_status($conn, $session, 'submitted', 'session_submitted');
                header('Location: attendance-session.php?id=' . $session_id . '&status=submitted');
                exit();
            } catch (RuntimeException | InvalidArgumentException $exception) {
                $error_msg = $exception->getMessage();
            }
        }
    } elseif ($action === 'approve') {
        if (!$is_controller || $session['status'] !== 'submitted' || $attendance_user_id === false) {
            $error_msg = 'Only an Attendance Controller can approve a submitted session.';
        } else {
            try {
                attendance_change_session_status($conn, $session, 'approved', 'session_approved');
                header('Location: attendance-session.php?id=' . $session_id . '&status=approved');
                exit();
            } catch (RuntimeException | InvalidArgumentException $exception) {
                $error_msg = $exception->getMessage();
            }
        }
    } elseif ($action === 'reopen') {
        $reason = attendance_post_string('reason');
        if (!$is_controller || $session['status'] !== 'approved' || $reason === '' || strlen($reason) > 500) {
            $error_msg = 'An Attendance Controller must provide a reason (up to 500 characters) to reopen an approved session.';
        } else {
            try {
                attendance_change_session_status($conn, $session, 'submitted', 'session_reopened', $reason);
                header('Location: attendance-session.php?id=' . $session_id . '&status=reopened');
                exit();
            } catch (RuntimeException | InvalidArgumentException $exception) {
                $error_msg = $exception->getMessage();
            }
        }
    } else {
        $error_msg = 'Unknown attendance action.';
    }
}

$session = attendance_get_session($conn, $session_id);
$can_record = attendance_can_record($session);
$checkin_open = $can_record && attendance_is_checkin_open($session);
$can_submit = attendance_can_submit($session);
$can_correct = $is_controller && $session['status'] === 'submitted';
$status_messages = [
    'recorded' => 'Attendance was saved.',
    'submitted' => 'The session was submitted for Controller review.',
    'approved' => 'The session was approved and locked.',
    'reopened' => 'The session was reopened for correction.',
];
if (isset($_GET['status']) && isset($status_messages[$_GET['status']])) {
    $notice_msg = $status_messages[$_GET['status']];
}

$counts_stmt = $conn->prepare(
    "SELECT COUNT(*) AS roster_count,
            SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) AS excused_count,
            SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) AS marked_absent_count
     FROM attendance_session_roster r
     LEFT JOIN attendance_records ar ON ar.session_id = r.session_id AND ar.member_id = r.member_id
     WHERE r.session_id = ?"
);
$counts_stmt->bind_param('i', $session_id);
$counts_stmt->execute();
$counts = $counts_stmt->get_result()->fetch_assoc();
$counts_stmt->close();

$roster_stmt = $conn->prepare(
    'SELECT m.id, m.full_Name, m.Phone_no, m.qr_token,
            ar.status AS attendance_status, ar.method AS attendance_method,
            ar.recorded_at, ar.reason, u.first_name AS recorder_first_name, u.last_name AS recorder_last_name
     FROM attendance_session_roster r
     JOIN members m ON m.id = r.member_id
     LEFT JOIN attendance_records ar ON ar.session_id = r.session_id AND ar.member_id = r.member_id
     LEFT JOIN users u ON u.id = ar.recorded_by
     WHERE r.session_id = ?
     ORDER BY m.full_Name, m.id'
);
$roster_stmt->bind_param('i', $session_id);
$roster_stmt->execute();
$roster = $roster_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$roster_stmt->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="attendance.php" class="text-decoration-none small"><i class="fas fa-arrow-left me-1"></i>All sessions</a>
        <h2 class="fw-bold text-dark mt-2 mb-1"><?php echo attendance_escape($session['title']); ?></h2>
        <p class="text-muted mb-0">
            <?php echo attendance_escape(date('M j, Y · g:i A', strtotime($session['session_start']))); ?>
            · <?php echo attendance_escape($session['location']); ?>
            · Holder: <?php echo attendance_escape(trim($session['holder_first_name'] . ' ' . $session['holder_last_name'])); ?>
            · Check-in: <?php echo attendance_escape(date('M j, g:i A', strtotime($session['checkin_opens_at']))); ?>
            – <?php echo attendance_escape(date('M j, g:i A', strtotime($session['checkin_closes_at']))); ?>
        </p>
    </div>
    <span class="badge fs-6 text-bg-<?php echo $session['status'] === 'approved' ? 'success' : ($session['status'] === 'submitted' ? 'warning' : 'primary'); ?>">
        <?php echo attendance_escape(ucfirst($session['status'])); ?>
    </span>
    <?php if (in_array($attendance_role, ['super admin', 'members manager', 'attendance controller'], true)): ?>
        <a class="btn btn-outline-primary" href="attendance-export.php?id=<?php echo (int)$session_id; ?>"><i class="fas fa-file-csv me-2"></i>Export CSV</a>
    <?php endif; ?>
</div>

<?php if ($error_msg !== ''): ?><div class="alert alert-danger"><?php echo attendance_escape($error_msg); ?></div><?php endif; ?>
<?php if ($notice_msg !== ''): ?><div class="alert alert-success"><?php echo attendance_escape($notice_msg); ?></div><?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Expected roster</small><strong class="fs-4"><?php echo (int)$counts['roster_count']; ?></strong></div></div>
    <div class="col-sm-3"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Present</small><strong class="fs-4 text-success"><?php echo (int)$counts['present_count']; ?></strong></div></div>
    <div class="col-sm-3"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Excused</small><strong class="fs-4 text-warning"><?php echo (int)$counts['excused_count']; ?></strong></div></div>
    <div class="col-sm-3"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Absent</small><strong class="fs-4 text-secondary"><?php echo max(0, (int)$counts['roster_count'] - (int)$counts['present_count'] - (int)$counts['excused_count']); ?></strong></div></div>
</div>

<?php if ($checkin_open): ?>
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold"><i class="fas fa-qrcode text-primary me-2"></i>Scan member QR code</h5>
            <p class="text-muted small">Camera access requires HTTPS or localhost. Each scan is checked against this session’s roster.</p>
            <div id="qr-reader" class="mb-2" style="max-width: 500px;"></div>
            <div id="qr-message" class="small text-muted" role="status">Starting camera…</div>
        </div>
    </div>
<?php elseif ($can_record): ?>
    <div class="alert alert-info">Check-in is available from <?php echo attendance_escape(date('g:i A', strtotime($session['checkin_opens_at']))); ?> until <?php echo attendance_escape(date('g:i A', strtotime($session['checkin_closes_at']))); ?>.</div>
<?php endif; ?>

<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="p-4 bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Member roster</h5>
            <small class="text-muted">Not checked in = absent, unless explicitly excused.</small>
        </div>
        <input type="search" class="form-control" id="roster-search" placeholder="Search name or phone" style="max-width: 280px;">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="roster-table">
            <thead class="table-light">
                <tr><th class="ps-4">Member</th><th>Phone</th><th>Attendance</th><th>Recorded</th><th class="text-end pe-4">Action</th></tr>
            </thead>
            <tbody>
                <?php if (!$roster): ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">The session roster is empty. Only active members are included when a session is created.</td></tr>
                <?php else: foreach ($roster as $member): ?>
                    <?php
                    $member_status = $member['attendance_status'] ?: 'absent';
                    $badge_class = $member_status === 'present' ? 'success' : ($member_status === 'excused' ? 'warning' : 'secondary');
                    $search_text = strtolower($member['full_Name'] . ' ' . $member['Phone_no']);
                    ?>
                    <tr data-member-search="<?php echo attendance_escape($search_text); ?>">
                        <td class="ps-4 fw-semibold"><?php echo attendance_escape($member['full_Name']); ?><small class="d-block text-muted">#<?php echo (int)$member['id']; ?></small></td>
                        <td><?php echo attendance_escape($member['Phone_no']); ?></td>
                        <td>
                            <span class="badge text-bg-<?php echo $badge_class; ?>"><?php echo attendance_escape(ucfirst($member_status)); ?></span>
                            <?php if ($member['attendance_method']): ?><small class="d-block text-muted"><?php echo attendance_escape($member['attendance_method']); ?></small><?php endif; ?>
                            <?php if ($member['reason']): ?><small class="d-block text-muted"><?php echo attendance_escape($member['reason']); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo $member['recorded_at'] ? attendance_escape(date('g:i A', strtotime($member['recorded_at']))) : '—'; ?></td>
                        <td class="text-end pe-4">
                            <?php if ($checkin_open): ?>
                                <?php if (!$member['attendance_status']): ?>
                                    <form method="post" class="d-inline-flex flex-wrap justify-content-end gap-1">
                                        <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
                                        <input type="hidden" name="action" value="record">
                                        <input type="hidden" name="member_id" value="<?php echo (int)$member['id']; ?>">
                                        <button class="btn btn-sm btn-outline-success" type="submit" name="status" value="present">Present</button>
                                        <button class="btn btn-sm btn-outline-warning" type="submit" name="status" value="excused">Excuse</button>
                                    </form>
                                <?php else: ?>
                                    <span class="small text-muted">Already recorded</span>
                                <?php endif; ?>
                            <?php elseif ($can_correct): ?>
                                <form method="post" class="d-flex flex-wrap justify-content-end gap-1">
                                    <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
                                    <input type="hidden" name="action" value="record">
                                    <input type="hidden" name="member_id" value="<?php echo (int)$member['id']; ?>">
                                    <select class="form-select form-select-sm" name="status" required style="width:auto;">
                                        <?php foreach (['present', 'excused', 'absent'] as $option): ?>
                                            <option value="<?php echo $option; ?>" <?php echo $member_status === $option ? 'selected' : ''; ?>><?php echo ucfirst($option); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="form-control form-control-sm" name="reason" maxlength="500" placeholder="Correction reason" required style="width: 170px;">
                                    <button class="btn btn-sm btn-primary" type="submit">Correct</button>
                                </form>
                            <?php else: ?>
                                <span class="small text-muted"><?php echo $member['recorder_first_name'] ? attendance_escape(trim($member['recorder_first_name'] . ' ' . $member['recorder_last_name'])) : '—'; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
    <?php if ($can_submit): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
            <input type="hidden" name="action" value="submit">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this session for Controller review?');">
                <i class="fas fa-paper-plane me-2"></i>Submit for review
            </button>
        </form>
    <?php endif; ?>
    <?php if ($is_controller && $session['status'] === 'submitted'): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-success" onclick="return confirm('Approve and lock this attendance session?');">
                <i class="fas fa-check me-2"></i>Approve and lock
            </button>
        </form>
    <?php endif; ?>
    <?php if ($is_controller && $session['status'] === 'approved'): ?>
        <form method="post" class="d-flex gap-2">
            <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
            <input type="hidden" name="action" value="reopen">
            <input class="form-control" name="reason" maxlength="500" placeholder="Reason to reopen" required>
            <button type="submit" class="btn btn-outline-danger">Reopen</button>
        </form>
    <?php endif; ?>
</div>

<?php if ($checkin_open): ?>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const qrMessage = document.getElementById('qr-message');
const qrCsrfToken = <?php echo json_encode(attendance_csrf_token()); ?>;
let scanPending = false;

async function submitQrToken(token) {
    if (scanPending) return;
    scanPending = true;
    qrMessage.textContent = 'Checking scanned code…';
    try {
        const response = await fetch('attendance-scan.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                csrf_token: qrCsrfToken,
                session_id: '<?php echo (int)$session_id; ?>',
                qr_token: token
            })
        });
        const result = await response.json();
        qrMessage.textContent = result.message;
        qrMessage.className = response.ok ? 'small text-success' : 'small text-danger';
        if (response.ok) setTimeout(() => window.location.reload(), 900);
    } catch (error) {
        qrMessage.textContent = 'Could not contact the attendance server. Check your connection and try again.';
        qrMessage.className = 'small text-danger';
        scanPending = false;
    }
}

if (typeof Html5Qrcode === 'undefined') {
    qrMessage.textContent = 'QR scanner library did not load. Use the manual roster below.';
    qrMessage.className = 'small text-danger';
} else {
    const qrScanner = new Html5Qrcode('qr-reader');
    qrScanner.start(
        {facingMode: 'environment'},
        {fps: 10, qrbox: {width: 240, height: 240}},
        decodedText => submitQrToken(decodedText.trim()),
        () => {}
    ).then(() => {
        qrMessage.textContent = 'Camera ready. Point it at a member QR code.';
    }).catch(() => {
        qrMessage.textContent = 'Camera could not start. Allow camera access or use the manual roster below.';
        qrMessage.className = 'small text-warning';
    });
}
</script>
<?php endif; ?>

<script>
document.getElementById('roster-search').addEventListener('input', event => {
    const query = event.target.value.trim().toLowerCase();
    document.querySelectorAll('#roster-table tbody tr[data-member-search]').forEach(row => {
        row.hidden = !row.dataset.memberSearch.includes(query);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
</body>
</html>
