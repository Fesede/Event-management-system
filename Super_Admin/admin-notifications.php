<?php
// ሴሽኑ አስቀድሞ ካልተጀመረ ብቻ እንዲጀምር ማድረግ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'include/conn.php';

// --- 2. አዲስ ኖቲፊኬሽን የመመዝገብ ሎጅክ (Add Notation Logic) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $type    = mysqli_real_escape_string($conn, $_POST['type']); // primary, danger, success, warning
    
    // እንደየአይነቱ አይኮን በራስ-ሰር መምረጥ
    $icon = "fa-bell";
    if ($type == 'danger') $icon = "fa-exclamation-circle";
    if ($type == 'success') $icon = "fa-check-circle";
    if ($type == 'warning') $icon = "fa-exclamation-triangle";

    $sql = "INSERT INTO notifications (title, message, type, icon, status) 
            VALUES ('$title', '$message', '$type', '$icon', 'unread')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-notifications.php");
        exit();
    }
}

// --- 3. ሁሉንም እንደተነበበ አድርግ (Mark All as Read) ---
if (isset($_POST['mark_read'])) {
    mysqli_query($conn, "UPDATE notifications SET status = 'read' WHERE status = 'unread'");
    header("Location: admin-notifications.php"); 
    exit();
}

// --- 4. ሁሉንም አጥፋ (Clear All) ---
if (isset($_POST['clear_all'])) {
    mysqli_query($conn, "DELETE FROM notifications");
    header("Location: admin-notifications.php"); 
    exit();
}

// ሁሉንም ኖቲፊኬሽኖች ከዳታቤዝ ማውጣት
$result = mysqli_query($conn, "SELECT * FROM notifications ORDER BY created_at DESC");

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    body { 
        background-color: #f8fafc; 
    }
    
    /* የካርዶቹ የግራ ጠርዝ መስመር ማስተካከያ */
    .border-left-primary { border-left: 5px solid #0d6efd !important; }
    .border-left-success { border-left: 5px solid #198754 !important; }
    .border-left-warning { border-left: 5px solid #ffc107 !important; }
    .border-left-danger { border-left: 5px solid #dc3545 !important; }
    .border-left-secondary { border-left: 5px solid #6c757d !important; }

    /* 💡 አላስፈላጊ ክፍተቶችን የሚያጠፋ የይዘት መያዣ */
    .content-wrapper-custom {
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<div class="content-wrapper-custom">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Notifications & Notations</h2>
            <p class="text-muted mb-0">Manage system alerts and official announcements.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addNotificationModal" style="background-color: #0d6efd; border-color: #0d6efd;">
                <i class="fas fa-plus-circle me-2"></i> Add New Notation
            </button>
            <form method="POST" class="m-0">
                <button type="submit" name="mark_read" class="btn btn-outline-primary btn-sm rounded-pill px-3">Mark All Read</button>
            </form>
            <form method="POST" class="m-0">
                <button type="submit" name="clear_all" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('ደውል! ሁሉንም ማጥፋት ትፈልጋለህ?')">Clear All</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    // የኖቲፊኬሽን አይነትን መሰረት አድርጎ ክላስ መምረጥ
                    $type = $row['type'] ?? 'primary';
                    $border_class = 'border-left-' . $type;
                ?>
                    <div class="card mb-3 border-0 shadow-sm <?php echo $border_class; ?>" style="border-radius: 12px; <?php echo ($row['status'] == 'read') ? 'opacity: 0.6;' : ''; ?>">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="rounded-circle bg-<?php echo $type; ?> bg-opacity-10 text-<?php echo $type; ?> p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas <?php echo htmlspecialchars($row['icon'] ?? 'fa-bell'); ?> fa-lg"></i>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </h6>
                                        <?php if(($row['status'] ?? 'unread') == 'unread'): ?>
                                            <span class="badge bg-danger" style="font-size: 9px; padding: 3px 6px;">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0 text-muted small text-truncate" style="max-width: 700px;">
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3 ms-4">
                                <small class="text-muted small text-nowrap"><?php echo isset($row['created_at']) ? date('M d, g:i A', strtotime($row['created_at'])) : ''; ?></small>
                                <a href="read_notation.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm rounded-pill px-3 border text-nowrap">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm p-5 border">
                    <i class="fas fa-bell-slash text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted fw-bold">No Notifications Found</h5>
                    <p class="text-muted small mb-0">System is clean. There are no active notations or alerts at this moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</main> <div class="modal fade" id="addNotificationModal" tabindex="-1" aria-labelledby="addNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold mb-0" id="addNotificationModalLabel"><i class="fas fa-bullhorn text-primary me-2"></i>Create New Notation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Notification Title (ርዕስ)</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Important Announcement" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alert Level / Type (ደረጃ)</label>
                        <select name="type" class="form-select" required>
                            <option value="primary">General / Info (ሰማያዊ)</option>
                            <option value="success">Success / Done (አረንጓዴ)</option>
                            <option value="warning">Warning / Attention (ቢጫ)</option>
                            <option value="danger">Urgent / Alert (ቀይ)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Message (መልዕክት)</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Write the announcement details here..." required></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_notification" class="btn btn-primary py-2 fw-bold rounded-pill px-4 shadow-sm">PUBLISH NOTATION</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>