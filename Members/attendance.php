<?php
$page_title = 'Attendance Dashboard';
$active_tab = 'attendance';
require_once 'includes/attendance.php';
include 'includes/connect.php';

attendance_require_roles([
    'super admin',
    'members manager',
    'attendance controller',
    'attendance holder',
]);

$required_tables = [
    'attendance_sessions',
    'attendance_session_roster',
    'attendance_records',
    'attendance_audit_log',
];
$table_names = "'" . implode("', '", $required_tables) . "'";
$schema_result = $conn->query(
    "SELECT table_name
     FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name IN ($table_names)"
);
$existing_tables = array_column($schema_result->fetch_all(MYSQLI_ASSOC), 'table_name');
$missing_tables = array_values(array_diff($required_tables, $existing_tables));

if ($missing_tables) {
    http_response_code(503);
    include 'includes/header.php';
    ?>
    <div class="alert alert-warning">
        <h2 class="h4">Attendance database setup is incomplete</h2>
        <p>The following tables are missing from the <strong>st_raguel</strong> database:</p>
        <p><code><?php echo attendance_escape(implode(', ', $missing_tables)); ?></code></p>
        <p class="mb-0">Complete the migration in <code>Members/database/attendance_migration.sql</code>. If the migration was only partly applied, resolve its SQL error before rerunning it.</p>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

$error_msg = '';
$attendance_user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
$can_create_sessions = in_array($attendance_role, ['super admin', 'attendance controller'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    attendance_require_roles(['super admin', 'attendance controller']);
    attendance_validate_csrf();

    $title = attendance_post_string('title');
    $location = attendance_post_string('location');
    $holder_id = filter_var($_POST['holder_user_id'] ?? null, FILTER_VALIDATE_INT);
    $event_id = empty($_POST['event_id']) ? null : filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
    $start = attendance_parse_local_datetime(attendance_post_string('session_start'));
    $checkin_opens = attendance_parse_local_datetime(attendance_post_string('checkin_opens_at'));
    $checkin_closes = attendance_parse_local_datetime(attendance_post_string('checkin_closes_at'));
    $valid_schedule = $start !== null
        && $checkin_opens !== null
        && $checkin_closes !== null
        && $checkin_closes > $checkin_opens
        && $start >= $checkin_opens
        && $start <= $checkin_closes;

    if ($attendance_user_id === false) {
        $error_msg = 'Your account must be linked to a database user before creating attendance sessions.';
    } elseif ($title === '' || strlen($title) > 180 || $location === '' || strlen($location) > 255 || $holder_id === false || !$valid_schedule || ($event_id !== null && $event_id === false)) {
        $error_msg = 'Enter a valid title, start time, check-in window, location, and attendance holder. The session start must fall inside the check-in window.';
    } else {
        $holder_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'Attendance Holder' AND status = 'active'");
        if (!$holder_stmt) {
            $error_msg = 'Could not prepare the attendance holder lookup: ' . $conn->error;
        } else {
            $holder_stmt->bind_param('i', $holder_id);
            $holder_stmt->execute();
            $holder_valid = $holder_stmt->get_result()->num_rows === 1;
            $holder_stmt->close();

            if (!$holder_valid) {
                $error_msg = 'Select an active user with the Attendance Holder role.';
            } elseif ($event_id !== null) {
                $event_stmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND status = 'published'");
                if (!$event_stmt) {
                    $error_msg = 'Could not prepare the event lookup: ' . $conn->error;
                } else {
                    $event_stmt->bind_param('i', $event_id);
                    $event_stmt->execute();
                    $event_valid = $event_stmt->get_result()->num_rows === 1;
                    $event_stmt->close();
                    if (!$event_valid) {
                        $error_msg = 'Select a published event or leave the event field blank.';
                    }
                }
            }

            if ($error_msg === '') {
                try {
                    $conn->begin_transaction();
                    $start_value = $start->format('Y-m-d H:i:s');
                    $checkin_opens_value = $checkin_opens->format('Y-m-d H:i:s');
                    $checkin_closes_value = $checkin_closes->format('Y-m-d H:i:s');
                    $stmt = $conn->prepare(
                        "INSERT INTO attendance_sessions (title, event_id, session_start, checkin_opens_at, checkin_closes_at, location, status, holder_user_id, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, 'open', ?, ?)"
                    );
                    if (!$stmt) {
                        throw new RuntimeException('Could not prepare the attendance session: ' . $conn->error);
                    }
                    $stmt->bind_param('sissssii', $title, $event_id, $start_value, $checkin_opens_value, $checkin_closes_value, $location, $holder_id, $attendance_user_id);
                    if (!$stmt->execute()) {
                        throw new RuntimeException('Could not create the attendance session: ' . $stmt->error);
                    }
                    $session_id = $stmt->insert_id;
                    $stmt->close();

                    $roster_stmt = $conn->prepare(
                        "INSERT INTO attendance_session_roster (session_id, member_id)
                         SELECT ?, id FROM members WHERE membership_status = 'active'"
                    );
                    if (!$roster_stmt) {
                        throw new RuntimeException('Could not prepare the session roster: ' . $conn->error);
                    }
                    $roster_stmt->bind_param('i', $session_id);
                    if (!$roster_stmt->execute()) {
                        throw new RuntimeException('Could not create the session roster: ' . $roster_stmt->error);
                    }
                    $roster_stmt->close();

                    attendance_log(
                        $conn,
                        $session_id,
                        null,
                        'session_created',
                        null,
                        [
                            'title' => $title,
                            'event_id' => $event_id,
                            'session_start' => $start_value,
                            'checkin_opens_at' => $checkin_opens_value,
                            'checkin_closes_at' => $checkin_closes_value,
                            'location' => $location,
                            'holder_user_id' => $holder_id,
                        ],
                        null
                    );
                    $conn->commit();
                    header('Location: attendance-session.php?id=' . $session_id);
                    exit();
                } catch (RuntimeException $exception) {
                    $conn->rollback();
                    $error_msg = $exception->getMessage();
                }
            }
        }
    }
}

$holders = [];
$events = [];
if ($can_create_sessions) {
    $holder_result = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'Attendance Holder' AND status = 'active' ORDER BY first_name, last_name");
    if (!$holder_result) {
        $error_msg = 'Could not load attendance holders: ' . $conn->error;
    } else {
        $holders = $holder_result->fetch_all(MYSQLI_ASSOC);
    }
    $event_result = $conn->query("SELECT id, title, event_date FROM events WHERE status = 'published' ORDER BY event_date DESC");
    if (!$event_result) {
        $error_msg = 'Could not load published events: ' . $conn->error;
    } else {
        $events = $event_result->fetch_all(MYSQLI_ASSOC);
    }
}

$session_filter = '';
$session_params = [];
$session_types = '';
if ($attendance_role === 'attendance holder') {
    if ($attendance_user_id === false) {
        http_response_code(403);
        exit('Your account must be linked to a database user.');
    }
    $session_filter = 'WHERE s.holder_user_id = ?';
    $session_params[] = $attendance_user_id;
    $session_types = 'i';
}

$sessions_stmt = $conn->prepare(
    "SELECT s.id, s.title, s.session_start, s.checkin_opens_at, s.checkin_closes_at,
            s.location, s.status, s.holder_user_id,
            u.first_name AS holder_first_name, u.last_name AS holder_last_name,
            COUNT(DISTINCT r.member_id) AS roster_count,
            COUNT(DISTINCT CASE WHEN ar.status = 'present' THEN ar.member_id END) AS present_count,
            COUNT(DISTINCT CASE WHEN ar.status = 'excused' THEN ar.member_id END) AS excused_count
     FROM attendance_sessions s
     LEFT JOIN users u ON u.id = s.holder_user_id
     LEFT JOIN attendance_session_roster r ON r.session_id = s.id
     LEFT JOIN attendance_records ar ON ar.session_id = s.id AND ar.member_id = r.member_id
     $session_filter
     GROUP BY s.id, s.title, s.session_start, s.checkin_opens_at, s.checkin_closes_at,
              s.location, s.status, s.holder_user_id,
              u.first_name, u.last_name
     ORDER BY s.session_start DESC
     LIMIT 100"
);
if (!$sessions_stmt) {
    $error_msg = 'Could not prepare the attendance dashboard: ' . $conn->error;
    $sessions = [];
} else {
    if ($session_params) {
        $sessions_stmt->bind_param($session_types, $session_params[0]);
    }
    if (!$sessions_stmt->execute()) {
        $error_msg = 'Could not load attendance sessions: ' . $sessions_stmt->error;
        $sessions = [];
    } else {
        $sessions = $sessions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $sessions_stmt->close();
}

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Attendance</h2>
        <p class="text-muted mb-0">
            <?php if ($attendance_role === 'attendance holder'): ?>
                Record attendance for your assigned sessions using the member roster or QR scanner.
            <?php elseif ($attendance_role === 'attendance controller' || $attendance_role === 'super admin'): ?>
                Create sessions, assign holders, and review submitted attendance.
            <?php else: ?>
                Review attendance sessions and member participation.
            <?php endif; ?>
        </p>
    </div>
    <?php if ($attendance_role === 'super admin'): ?>
        <a class="btn btn-outline-secondary" href="attendance-audit.php"><i class="fas fa-list-check me-2"></i>Audit log</a>
    <?php endif; ?>
</div>

<?php if ($error_msg !== ''): ?>
    <div class="alert alert-danger"><?php echo attendance_escape($error_msg); ?></div>
<?php endif; ?>

<?php
$session_total = count($sessions);
$review_total = 0;
$present_total = 0;
$roster_total = 0;
foreach ($sessions as $summary_session) {
    $review_total += $summary_session['status'] === 'submitted' ? 1 : 0;
    $present_total += (int)$summary_session['present_count'];
    $roster_total += (int)$summary_session['roster_count'];
}
?>
<div class="row g-3 mb-4">
    <div class="col-sm-4"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Sessions</small><strong class="fs-4"><?php echo $session_total; ?></strong></div></div>
    <div class="col-sm-4"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted"><?php echo $attendance_role === 'attendance holder' ? 'Your submissions awaiting review' : 'Awaiting review'; ?></small><strong class="fs-4 text-warning"><?php echo $review_total; ?></strong></div></div>
    <div class="col-sm-4"><div class="card card-custom border-0 shadow-sm p-3"><small class="text-muted">Present across shown sessions</small><strong class="fs-4 text-success"><?php echo $present_total; ?> <small class="fs-6 text-muted">/ <?php echo $roster_total; ?></small></strong></div></div>
</div>

<?php if ($can_create_sessions): ?>
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-calendar-plus text-primary me-2"></i>Create attendance session</h5>
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
                <div class="col-md-6">
                    <label class="form-label" for="session-title">Session title</label>
                    <input class="form-control" id="session-title" name="title" maxlength="180" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="session-event">Published event (optional)</label>
                    <select class="form-select" id="session-event" name="event_id">
                        <option value="">No linked event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo (int)$event['id']; ?>"><?php echo attendance_escape($event['title'] . ' — ' . $event['event_date']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="session-start">Date and time</label>
                    <input type="datetime-local" class="form-control" id="session-start" name="session_start" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="checkin-opens">Check-in opens</label>
                    <input type="datetime-local" class="form-control" id="checkin-opens" name="checkin_opens_at" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="checkin-closes">Check-in closes</label>
                    <input type="datetime-local" class="form-control" id="checkin-closes" name="checkin_closes_at" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="session-location">Location</label>
                    <input class="form-control" id="session-location" name="location" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="session-holder">Attendance Holder</label>
                    <select class="form-select" id="session-holder" name="holder_user_id" required>
                        <option value="">Select a holder</option>
                        <?php foreach ($holders as $holder): ?>
                            <option value="<?php echo (int)$holder['id']; ?>"><?php echo attendance_escape($holder['first_name'] . ' ' . $holder['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$holders): ?>
                    <div class="col-12">
                        <div class="alert alert-warning mb-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <span>Create an active Attendance Holder account before creating a session. Add one from Super Admin → Users, then return here.</span>
                            <?php if ($attendance_role === 'super admin'): ?>
                                <a class="btn btn-sm btn-outline-primary" href="../Super_Admin/admin-users.php">
                                    <i class="fas fa-user-plus me-1"></i>Create Holder account
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-12">
                    <button class="btn btn-primary px-4" type="submit" name="create_session" <?php echo !$holders ? 'disabled' : ''; ?>>
                        <i class="fas fa-plus me-2"></i>Create session and capture active member roster
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Sessions</h5>
        <span class="badge bg-primary-subtle text-primary"><?php echo count($sessions); ?> shown</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Session</th>
                    <th>Date and location</th>
                    <th>Holder</th>
                    <th>Present / Roster</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$sessions): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <?php if ($attendance_role === 'attendance holder'): ?>
                                No sessions have been assigned to you yet. Ask an Attendance Controller to create one and assign it to your account.
                            <?php elseif ($can_create_sessions): ?>
                                No attendance sessions yet. Create a session above to start taking attendance.
                            <?php else: ?>
                                No attendance sessions are available yet.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: foreach ($sessions as $session): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?php echo attendance_escape($session['title']); ?></td>
                        <td><?php echo attendance_escape(date('M j, Y · g:i A', strtotime($session['session_start']))); ?><small class="d-block text-muted"><?php echo attendance_escape($session['location']); ?></small><small class="d-block text-muted">Check-in: <?php echo attendance_escape(date('g:i A', strtotime($session['checkin_opens_at']))); ?>–<?php echo attendance_escape(date('g:i A', strtotime($session['checkin_closes_at']))); ?></small></td>
                        <td><?php echo attendance_escape(trim($session['holder_first_name'] . ' ' . $session['holder_last_name'])); ?></td>
                        <td><?php echo (int)$session['present_count']; ?> / <?php echo (int)$session['roster_count']; ?></td>
                        <td><span class="badge text-bg-<?php echo $session['status'] === 'approved' ? 'success' : ($session['status'] === 'submitted' ? 'warning' : 'primary'); ?>"><?php echo attendance_escape(ucfirst($session['status'])); ?></span></td>
                        <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="attendance-session.php?id=<?php echo (int)$session['id']; ?>">Open</a></td>
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
