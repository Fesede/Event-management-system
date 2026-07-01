<?php
include 'include/conn.php';
include 'include/header.php';
include 'include/nav.php';

// 💡 ከዳታቤዝ የዜና (News) መረጃዎችን መጫኛ ኩዌሪ
$sql = "SELECT * FROM `news` ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<style>
    body { 
        background-color: #f8fafc; 
    }
    
    .news-preview-img {
        width: 55px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* 💡 የዜና ሁኔታዎች (Status Badges) ቀለማት */
    .badge-published { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .badge-draft { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-rejected { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    
    /* ረጅም ፅሁፎችን በአንድ መስመር ብቻ ለመገደብ ማሳጠሪያ */
    .text-truncate-custom {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        max-width: 250px;
    }

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
            <h2 class="fw-bold text-dark mb-1">News & Articles</h2>
            <p class="text-muted mb-0">Publish announcements and community stories for St.Raguel Church.</p>
        </div>
        <a href="form.php" class="btn btn-danger px-4 shadow-sm" style="background-color: #be123c; border-color: #be123c;">
            <i class="fas fa-plus me-2"></i> Create New Post
        </a>
    </div>
</div>

<div class="card card-table-wrapper p-2">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 text-secondary small fw-bold text-uppercase border-0">Post</th>
                    <th class="text-secondary small fw-bold text-uppercase border-0">Category</th>
                    <th class="text-secondary small fw-bold text-uppercase border-0">Author</th>
                    <th class="text-secondary small fw-bold text-uppercase border-0">Date Published</th>
                    <th class="text-secondary small fw-bold text-uppercase border-0">Status</th>
                    <th class="text-end pe-4 text-secondary small fw-bold text-uppercase border-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = strtolower(trim($row['status']));
                        
                        // የስትատስ ከለር መምረጫ ሁኔታ
                        if ($status === 'published' || $status === 'active') {
                            $status_class = 'badge-published';
                            $status_icon = '<i class="fas fa-check-circle me-1"></i>';
                        } elseif ($status === 'draft') {
                            $status_class = 'badge-draft';
                            $status_icon = '<i class="fas fa-file-alt me-1"></i>';
                        } else {
                            $status_class = 'badge-rejected';
                            $status_icon = '<i class="fas fa-times-circle me-1"></i>';
                        }
                ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?? ''); ?>" class="news-preview-img me-3" onerror="this.src='../Admin/assets/image/logo.png'">
                            <div>
                                <span class="fw-bold text-dark d-block small"><?php echo htmlspecialchars($row['title'] ?? ''); ?></span>
                                <small class="text-muted text-truncate-custom" style="font-size: 12px;"><?php echo htmlspecialchars(strip_tags($row['content'] ?? '')); ?></small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border fw-medium px-2 py-1"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></span></td>
                    <td class="small text-dark fw-semibold"><?php echo htmlspecialchars($row['author'] ?? 'Admin'); ?></td>
                    <td class="small text-muted"><?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : ''; ?></td>
                    <td>
                        <span class="badge <?php echo $status_class; ?> d-inline-flex align-items-center px-2 py-1 small fw-bold text-uppercase" style="font-size: 11px;">
                            <?php echo $status_icon . htmlspecialchars($row['status'] ?? ''); ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group gap-1">
                            <a href="view_news.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="View"><i class="fas fa-eye text-muted"></i></a>
                            <a href="edit_news.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                            <a href="reject_news.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm border" title="Delete" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fas fa-trash text-danger"></i></a>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="6" class="text-center py-4 text-muted">No news or articles found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</main> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>