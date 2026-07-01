<?php
include "include/conn.php";

// 1. GET THE ID AND FETCH DATA
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Join or simple select to get the full news data
    $sql = "SELECT * FROM news WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $news = mysqli_fetch_assoc($result);

    if (!$news) {
        die("<div class='container mt-5 alert alert-danger'>News article not found!</div>");
    }
} else {
    die("<div class='container mt-5 alert alert-warning'>No ID specified.</div>");
}

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    :root { --main-blue: #0d47a1; --bg-gray: #f4f7f6; }
    body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; line-height: 1.6; }
    .article-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; margin-top: 30px; border: none; }
    .article-image { width: 100%; max-height: 500px; object-fit: cover; }
    .category-badge { background: var(--main-blue); color: white; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
    .status-indicator { font-size: 0.9rem; font-weight: 600; }
    .content-area { font-size: 1.1rem; color: #333; white-space: pre-line; /* Preserves line breaks from textarea */ }
    .meta-info { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
</style>

<div class="container pb-5">
    <!-- Back Button -->
    <div class="mt-4">
        <a href="admin-news.php" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-2"></i> Back to News List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <article class="article-card">
                <!-- 2. ARTICLE IMAGE -->
                <img src="../uploads/<?php echo $news['image']; ?>" class="article-image" alt="News Image" onerror="this.src='../assets/placeholder.jpg'">

                <div class="p-4 p-md-5">
                    <!-- 3. ARTICLE HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="category-badge"><?php echo htmlspecialchars($news['category']); ?></span>
                        <span class="status-indicator <?php echo ($news['status'] == 'published') ? 'text-success' : 'text-danger'; ?>">
                            <i class="fas fa-circle me-1 small"></i> <?php echo ucfirst($news['status']); ?>
                        </span>
                    </div>

                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($news['title']); ?></h1>

                    <div class="meta-info d-flex align-items-center text-muted">
                        <div class="me-4">
                            <i class="fas fa-user-circle me-2"></i> By <strong><?php echo htmlspecialchars($news['author']); ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-calendar-alt me-2"></i> ID: #<?php echo $news['id']; ?>
                        </div>
                    </div>

                    <!-- 4. ARTICLE CONTENT -->
                    <div class="content-area">
                        <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                    </div>

                    <!-- 5. ACTIONS AREA -->
                    <div class="mt-5 pt-4 border-top">
                        <div class="d-flex gap-2">
                            <a href="edit_news.php?id=<?php echo $news['id']; ?>" class="btn btn-primary px-4">
                                <i class="fas fa-edit me-2"></i> Edit Article
                            </a>
                            <a href="reject_news.php?id=<?php echo $news['id']; ?>" 
                               class="btn btn-outline-danger px-4"
                               onclick="return confirm('Are you sure you want to reject this article?');">
                                <i class="fas fa-ban me-2"></i> Reject
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>