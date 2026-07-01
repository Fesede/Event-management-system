<?php
include "include/conn.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    // Update status to read
    mysqli_query($conn, "UPDATE notifications SET status = 'read' WHERE id = '$id'");
    
    $result = mysqli_query($conn, "SELECT * FROM notifications WHERE id = '$id'");
    $note = mysqli_fetch_assoc($result);
    if (!$note) die("Not found.");
}

include 'include/header.php';
include 'include/nav.php';
?>

<div id="main-content" style="margin-left: 250px; padding: 40px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="bg-<?php echo $note['type']; ?> p-5 text-center text-white">
                        <i class="fas <?php echo $note['icon']; ?> fa-4x mb-3"></i>
                        <h1 class="fw-bold mb-0"><?php echo htmlspecialchars($note['title']); ?></h1>
                    </div>
                    <div class="card-body p-5 bg-white">
                        <div class="d-flex justify-content-between mb-4 border-bottom pb-3 text-muted">
                            <span><i class="fas fa-calendar-alt me-2"></i><?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                            <span><i class="fas fa-hashtag me-2"></i>Ref: <?php echo $note['id']; ?></span>
                        </div>
                        <div style="font-size: 1.2rem; line-height: 1.8; color: #444;">
                            <?php echo nl2br(htmlspecialchars($note['message'])); ?>
                        </div>
                        <div class="mt-5 pt-4 border-top">
                            <a href="admin-notifications.php" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>