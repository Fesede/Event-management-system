<?php
// ሴሽኑ አስቀድሞ ካልተጀመረ ብቻ እንዲጀምር ማድረግ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'include/conn.php';

$notification = null;

// Validate and process the incoming notification ID parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Mark this specific notification as read automatically upon viewing
    $update_sql = "UPDATE notifications SET status = 'read' WHERE id = $id";
    mysqli_query($conn, $update_sql);

    // 2. Fetch the notification payload details
    $sql = "SELECT * FROM notifications WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $notification = mysqli_fetch_assoc($result);
    }
}

include 'include/header.php';
include 'include/A_nav.php';
?>

<style>
    :root { 
        --sidebar-offset: 260px; 
    }
    
    body { 
        background-color: #f8fafc; 
        font-family: 'Inter', sans-serif;
    }
    
    .content-wrapper-custom {
        margin-left: var(--sidebar-offset) !important;
        padding: 40px !important;
        min-height: 100vh;
    }

    .notation-view-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
    }

    .icon-badge-large {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Message body block detailing context */
    .message-container {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 24px;
        white-space: pre-line; /* Preserves paragraph line breaks from database */
        color: #334155;
        font-size: 1.05rem;
        line-height: 1.6;
    }

    .meta-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 600;
    }
</style>

<main class="content-wrapper-custom">
    <div class="mb-4">
        <a href="admin-notifications.php" class="btn btn-link text-decoration-none p-0 text-secondary fw-semibold">
            <i class="fas fa-arrow-left me-2"></i> Back to Notifications
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 col-xl-7">
            <?php if ($notification): 
                $type = $notification['type'] ?? 'primary';
                $icon = htmlspecialchars($notification['icon'] ?? 'fa-bell');
            ?>
                <div class="card notation-view-card border-0 shadow-sm p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-badge-large bg-<?php echo $type; ?> bg-opacity-10 text-<?php echo $type; ?>">
                            <i class="fas <?php echo $icon; ?> fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 pt-1">
                            <h3 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($notification['title']); ?></h3>
                            
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="badge bg-<?php echo $type; ?> text-capitalize px-3 py-2 rounded-pill small">
                                    <?php echo $type; ?> alert
                                </span>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> 
                                    <?php echo date('M d, Y', strtotime($notification['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i> 
                                    <?php echo date('g:i A', strtotime($notification['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-3">

                    <div class="mb-2">
                        <span class="meta-label d-block mb-2">Message Payload</span>
                        <div class="message-container">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5 border border-dashed" style="border-radius: 16px !important;">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3" style="opacity: 0.7;"></i>
                    <h5 class="text-dark fw-bold">Notation Not Found</h5>
                    <p class="text-muted small mb-4">The dynamic file record you are tracking down doesn't exist or may have been permanently cleared by an system administrator.</p>
                    <a href="admin-notifications.php" class="btn btn-primary btn-sm rounded-pill px-4" style="background: #4f46e5; border-color: #4f46e5;">
                        Return to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>