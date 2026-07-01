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
include 'include/A_nav.php';
?>

<style>
    :root { 
        --sidebar-offset: 260px; /* Adjust this to match your exact sidebar width */
    }
    
    body { 
        background-color: #f8fafc; 
        font-family: 'Inter', sans-serif;
    }
    
    /* 💡 Crucial Fix: Pushes content out from underneath the fixed sidebar */
    .content-wrapper-custom {
        margin-left: var(--sidebar-offset) !important;
        padding: 40px !important;
        min-height: 100vh;
        transition: all 0.3s ease;
    }

    /* Modern Minimalist Card Styling */
    .notification-card {
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff;
        border-radius: 14px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04) !important;
    }

    /* Elegant left border accents */
    .border-left-primary { border-left: 5px solid #4f46e5 !important; }
    .border-left-success { border-left: 5px solid #10b981 !important; }
    .border-left-warning { border-left: 5px solid #f59e0b !important; }
    .border-left-danger { border-left: 5px solid #ef4444 !important; }
    .border-left-secondary { border-left: 5px solid #64748b !important; }

    /* Circular Icon Badges */
    .icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Clean typography and button overrides */
    .btn-action-group .btn {
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    .text-truncate-custom {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #64748b;
    }
</style>

<main class="content-wrapper-custom">
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Notifications & Notations</h2>
            <p class="text-muted mb-0 small">Manage system alerts and official announcements.</p>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2 btn-action-group">
            <form method="POST" class="m-0">
                <button type="submit" name="mark_read" class="btn btn-outline-secondary rounded-pill px-3">Mark All Read</button>
            </form>
            <form method="POST" class="m-0">
                <button type="submit" name="clear_all" class="btn btn-outline-danger rounded-pill px-3" onclick="return confirm('ደውል! ሁሉንም ማጥፋት ትፈልጋለህ?')">Clear All</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $type = $row['type'] ?? 'primary';
                    $border_class = 'border-left-' . $type;
                    $is_read = ($row['status'] == 'read');
                ?>
                    <div class="card mb-3 border-0 shadow-sm <?php echo $border_class; ?> notification-card" style="<?php echo $is_read ? 'opacity: 0.65; background-color: #f8fafc;' : ''; ?>">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap gap-3">
                            <div class="d-flex align-items-center flex-grow-1 gap-3">
                                <div class="icon-badge bg-<?php echo $type; ?> bg-opacity-10 text-<?php echo $type; ?>">
                                    <i class="fas <?php echo htmlspecialchars($row['icon'] ?? 'fa-bell'); ?> fa-lg"></i>
                                </div>
                                
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </h6>
                                        <?php if(!$is_read): ?>
                                            <span class="badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 4px 8px; letter-spacing: 0.5px;">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0 small text-truncate-custom" style="max-width: 850px;">
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 w-100 w-md-auto border-top border-md-top-0 pt-2 pt-md-0">
                                <small class="text-muted small text-nowrap" style="font-size: 0.8rem;">
                                    <i class="far fa-clock me-1"></i><?php echo isset($row['created_at']) ? date('M d, g:i A', strtotime($row['created_at'])) : ''; ?>
                                </small>
                                <a href="read_notation.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm rounded-pill px-3 border text-nowrap" style="font-weight: 600; font-size: 0.8rem; background: #fff;">
                                    <i class="fas fa-eye me-1 text-muted"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5 border border-dashed" style="border-radius: 16px !important;">
                    <i class="fas fa-bell-slash text-muted fa-3x mb-3" style="opacity: 0.4;"></i>
                    <h5 class="text-dark fw-bold">No Notifications Found</h5>
                    <p class="text-muted small mb-0">System is clean. There are no active notations or alerts right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main> 

<div class="modal fade" id="addNotificationModal" tabindex="-1" aria-labelledby="addNotificationModalLabel" aria-hidden="true">
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
                        <input type="text" name="title" class="form-control" placeholder="e.g., Important Announcement" required style="border-radius: 8px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alert Level / Type (ደረጃ)</label>
                        <select name="type" class="form-select" required style="border-radius: 8px;">
                            <option value="primary">General / Info (ሰማያዊ)</option>
                            <option value="success">Success / Done (አረንጓዴ)</option>
                            <option value="warning">Warning / Attention (ቢጫ)</option>
                            <option value="danger">Urgent / Alert (ቀይ)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Message (መልዕክት)</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Write the announcement details here..." required style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_notification" class="btn btn-primary py-2 fw-bold rounded-pill px-4 shadow-sm" style="background: #4f46e5; border-color: #4f46e5;">PUBLISH NOTATION</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>