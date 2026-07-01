<?php
include 'include/header.php';
include 'include/A_nav.php';
include 'include/conn.php';

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$current_user = $_SESSION['user_name'] ?? 'Admin User';

// 1. Fetch "My News" using Prepared Statements (Selecting 'category')
$my_news_query = "SELECT id, title, category, status, created_at FROM news WHERE author = ? ORDER BY created_at DESC";
$stmt1 = mysqli_prepare($conn, $my_news_query);
mysqli_stmt_bind_param($stmt1, "s", $current_user);
mysqli_stmt_execute($stmt1);
$my_news = mysqli_stmt_get_result($stmt1);

// 2. Fetch "Community News" (Added 'status' to the SELECT string explicitly)
$other_news_query = "SELECT title, category, status, author, created_at FROM news WHERE author != ? AND LOWER(status) = 'published' ORDER BY created_at DESC";
$stmt2 = mysqli_prepare($conn, $other_news_query);
mysqli_stmt_bind_param($stmt2, "s", $current_user);
mysqli_stmt_execute($stmt2);
$other_news = mysqli_stmt_get_result($stmt2);
?>

<style>
    :root { --primary: #4f46e5; --sidebar-width: 260px; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; }
    .main-content { margin-left: var(--sidebar-width); padding: 40px; }
    
    .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-add { background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
    
    .section-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 30px 0 15px 0; }
    .table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 30px; }
    
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; text-align: left; padding: 14px; font-size: 0.75rem; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; }
    td { padding: 14px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
    .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: inline-block; }
    .published { background: #dcfce7; color: #166534; }
    .draft { background: #fef3c7; color: #92400e; }
</style>

<div class="main-content">
    <div class="header-flex">
        <h1 style="margin:0; font-size: 1.5rem;">News Dashboard</h1>
        <a href="form.php" class="btn-add">+ Post News</a>
    </div>

    <div class="section-title">Community News</div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Headline</th>
                    <th>Category / ክፍል</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($other_news && mysqli_num_rows($other_news) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($other_news)): ?>
                    <?php 
                        $display_other_category = $row['category'] ?? $row['catagory'] ?? 'General'; 
                        $clean_status = strtolower(trim($row['status'] ?? 'published')); 
                        $display_status = htmlspecialchars($row['status'] ?? 'Published');
                    ?>
                    <tr>
                        <td><span style="color:var(--primary); font-weight:600;">@<?= htmlspecialchars($row['author']) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                        <td><?= htmlspecialchars($display_other_category) ?></td>
                        <td><span class="status-badge <?= htmlspecialchars($clean_status) ?>"><?= $display_status ?></span></td>
                        <td><?= date('H:i', strtotime($row['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 30px;">No community news updates available right now.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>