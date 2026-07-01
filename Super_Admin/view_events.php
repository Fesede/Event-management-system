<?php
include 'include/conn.php';
include 'include/header.php';
include 'include/nav.php';

// 💡 Fixed: Changed $_get to $_GET to correctly capture the URL parameter
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<div class='alert alert-danger m-4'>Error: Missing event identification code.</div>";
    exit;
}

$event_id = intval($_GET['id']);

// Fetch the specific event safely using prepared statements
$stmt = mysqli_prepare($conn, "SELECT * FROM `events` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

if (!$event) {
    echo "<div class='alert alert-warning m-4'>The requested event could not be found.</div>";
    exit;
}

// Resolve variables
$status = strtolower(trim($event['status'] ?? 'draft'));
if ($status === 'published' || $status === 'active') {
    $status_class = 'bg-success-subtle text-success border-success-subtle';
} else {
    $status_class = 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
}
$event_date = $event['created_at'] ?? $event['date'] ?? $event['event_date'] ?? 'N/A';
?>

<style>
    body { background-color: #f8fafc; }
    .view-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .hero-img {
        max-height: 400px;
        object-fit: cover;
        border-radius: 12px;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="container py-4">
    <!-- Breadcrumb / Back action navigation wrapper -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="admin-events.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
        <div class="btn-group gap-2">
            <a href="edit_events.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="card view-card p-4 p-md-5">
        <div class="row g-4">
            
            <!-- Image viewport -->
            <div class="col-lg-5 text-center">
                <img src="../uploads/<?php echo htmlspecialchars($event['image'] ?? ''); ?>" 
                     class="hero-img img-fluid" 
                     onerror="this.src='../Admin/assets/image/logo.png'">
            </div>
            
            <!-- Text contents layout -->
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-7 fw-medium">
                        <i class="fas fa-tag text-muted me-1"></i> <?php echo htmlspecialchars($event['category'] ?? 'General'); ?>
                    </span>
                    <span class="badge border px-3 py-2 fs-7 fw-bold <?php echo $status_class; ?>">
                        <?php echo ucfirst($status); ?>
                    </span>
                </div>

                <h1 class="display-6 fw-bold text-dark mb-3"><?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?></h1>
                
                <hr class="my-4 text-muted opacity-25">

                <!-- Metadata parameters distribution rows -->
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Scheduled Datetime</div>
                        <div class="text-dark fw-medium">
                            <i class="far fa-calendar-alt text-primary me-2"></i>
                            <?php echo $event_date !== 'N/A' ? date('F j, Y — g:i a', strtotime($event_date)) : 'Unspecified'; ?>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Venue Location</div>
                        <div class="text-dark fw-medium">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <?php echo htmlspecialchars($event['location'] ?? 'Church Sanctuary'); ?>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="info-label mb-2">Detailed Description</div>
                        <div class="bg-light p-3 rounded-3 text-secondary lh-lg" style="white-space: pre-line;">
                            <?php 
                            if (!empty($event['description'])) {
                                echo htmlspecialchars($event['description']);
                            } else {
                                echo "<em>No formal description was logged for this entry structure.</em>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>