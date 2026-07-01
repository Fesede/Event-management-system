<?php
include 'include/conn.php';
include 'include/header.php';
include 'include/nav.php';

// 💡 ስህተቱን ለማጥፋት 'date_time' የሚለውን ወደ 'created_at' ቀይረነዋል
$sql = "SELECT * FROM `events` ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<style>
    body { 
        background-color: #f8fafc; 
    }
    
    .event-preview-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* የኤቨንት ሁኔታዎች (Status Badges) ቀለማት */
    .badge-published { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .badge-draft { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    
    /* 💡 ክፍተቱን የሚያጠፋው ዋናው የካርድ ስታይል */
    .card-table-wrapper {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin: 0 !important; /* ምንም አይነት ትርፍ የውጭ ማርጂን እንዳይኖር */
    }
</style>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">Manage Events</h2>
            <p class="text-muted mb-0">Create, edit, or remove community events for the church.</p>
        </div>
        <a href="form.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #0d6efd; border-color: #0d6efd;">
            <i class="fas fa-plus me-2"></i> Create New Event
        </a>
    </div>
</div>

<div class="card card-table-wrapper p-2">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 border-0">Preview</th>
                    <th class="border-0">Event Title</th>
                    <th class="border-0">Category</th>
                    <th class="border-0">Date & Time</th>
                    <th class="border-0">Status</th>
                    <th class="text-end pe-4 border-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = strtolower(trim($row['status'] ?? 'draft'));
                        
                        // የስትատስ ከለር መምረጫ ሁኔታ
                        if ($status === 'published' || $status === 'active') {
                            $status_class = 'badge-published';
                            $status_icon = '<i class="fas fa-check-circle me-1"></i>';
                        } else {
                            $status_class = 'badge-draft';
                            $status_icon = '<i class="fas fa-file-alt me-1"></i>';
                        }
                        
                        // 💡 በዳታቤዝህ ላይ ያለው የቀን አምድ ስም 'created_at' ወይም 'date' መሆኑን አይቶ የሚመርጥ አማራጭ
                        $event_date = $row['created_at'] ?? $row['date'] ?? $row['event_date'] ?? '2026-05-14 21:02:00';
                ?>
                <tr>
                    <td class="ps-4">
                        <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?? ''); ?>" class="event-preview-img" onerror="this.src='../Admin/assets/image/logo.png'">
                    </td>
                    <td>
                        <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($row['title'] ?? ''); ?></span>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($row['location'] ?? 'Church'); ?></small>
                    </td>
                    <td><span class="badge bg-light text-dark border fw-medium px-2 py-1"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></span></td>
                    <td class="small text-muted"><?php echo date('Y-m-d H:i', strtotime($event_date)); ?></td>
                    <td>
                        <span class="badge <?php echo $status_class; ?> d-inline-flex align-items-center px-2 py-1 small fw-bold text-uppercase" style="font-size: 11px;">
                            <?php echo $status_icon . ucfirst($status); ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group gap-1">
                            <!-- 💡 አዲስ የተጨመረው የዕይታ (View) ቁልፍ -->
                            <a href="view_events.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="View"><i class="fas fa-eye text-success"></i></a>
                            <a href="edit_events.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                            <a href="delete_events.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="Delete" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fas fa-trash text-danger"></i></a>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="6" class="text-center py-4 text-muted">No events found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</main> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>