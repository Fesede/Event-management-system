<?php 
include 'include/conn.php';

// --- FETCH DYNAMIC STATS ---
// 1. Total Active Users
$user_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$total_users = mysqli_fetch_assoc($user_count_res)['total'];

// 2. Total Events
$event_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM events");
$total_events = ($event_count_res) ? mysqli_fetch_assoc($event_count_res)['total'] : 0;

// 3. New Members Today
$today_users_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
$new_today = mysqli_fetch_assoc($today_users_res)['total'];

// 4. Fetch Recent Users for the display table
$recent_users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// 💡 ሄደሩን በትክክለኛው መንገድ እዚህ እናስገባዋለን
include 'include/header.php';
?>

<style>
    body { 
        background-color: #f8fafc; 
    }
    
    /* Stat Cards Styling */
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }
    .stat-card i {
        position: absolute;
        right: 15px;
        bottom: 10px;
        font-size: 3rem;
        opacity: 0.15;
    }

    /* Table & Action Cards Styling */
    .dashboard-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .user-img {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        object-fit: cover;
    }

    /* 💡 ቋሚ እና የሀገር ውስጥ የአቫታር ሳጥን ስታይል (ኢንተርኔት አይፈልግም) */
    .user-icon-fallback {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background-color: #be123c;
        color: #ffffff;
        font-size: 1rem;
    }
    
    /* የሱፐር አድሚን ልዩ ቀይ ቀለም ለካርዶች */
    .bg-super-primary {
        background-color: #be123c !important;
    }
    .btn-super-primary {
        background-color: #be123c !important;
        border-color: #be123c !important;
        color: #ffffff !important;
    }
    .btn-super-primary:hover {
        background-color: #9f1239 !important;
        border-color: #9f1239 !important;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark mb-1">Welcome Back, Super Admin</h2>
        <p class="text-muted">Here is what's happening with St.Raguel Church platform today.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card stat-card bg-super-primary text-white h-100 p-4">
            <h6 class="text-uppercase small fw-bold opacity-75">Active Users</h6>
            <h2 class="fw-bold mb-1" style="font-size: 2.2rem;"><?= number_format($total_users) ?></h2>
            <p class="small mb-0 opacity-75">Verified accounts in system</p>
            <i class="fas fa-users"></i>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-success text-white h-100 p-4">
            <h6 class="text-uppercase small fw-bold opacity-75">Total Events</h6>
            <h2 class="fw-bold mb-1" style="font-size: 2.2rem;"><?= number_format($total_events) ?></h2>
            <p class="small mb-0 opacity-75">Planned & Live Events</p>
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-info text-white h-100 p-4">
            <h6 class="text-uppercase small fw-bold opacity-75">New Today</h6>
            <h2 class="fw-bold mb-1" style="font-size: 2.2rem;"><?= $new_today ?></h2>
            <p class="small mb-0 opacity-75">Signups in last 24 hours</p>
            <i class="fas fa-user-plus"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card dashboard-card p-2">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Recent Members</h5>
                <a href="admin-users.php" class="btn btn-sm btn-light border px-3">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-3 text-secondary small fw-bold text-uppercase">User</th>
                            <th class="border-0 text-secondary small fw-bold text-uppercase">Category</th>
                            <th class="border-0 text-secondary small fw-bold text-uppercase">Role</th>
                            <th class="border-0 text-secondary small fw-bold text-uppercase">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 position-relative d-flex align-items-center">
                                        
                                        <?php 
                                        // 💡 የፎቶ ፋይሉ በ uploads/ ፎልደር ውስጥ በአካል መኖሩን ማረጋገጫ (የ 404 ስህተትን ለመከላከል)
                                        $photo_path = 'uploads/' . $user['photo'];
                                        if (!empty($user['photo']) && file_exists($photo_path)): 
                                        ?>
                                            <img src="<?= $photo_path ?>" class="user-img" alt="User"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.setProperty('display', 'flex', 'important');">
                                        <?php else: ?>
                                            <style> .show-fallback-<?= $user['id'] ?> { display: flex !important; } </style>
                                        <?php endif; ?>
                                        
                                        <div class="user-icon-fallback align-items-center justify-content-center <?='show-fallback-' . $user['id']?>" style="display: none;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border fw-medium px-2 py-1"><?= htmlspecialchars($user['category']) ?></span></td>
                            <td><span class="small text-secondary fw-semibold"><?= htmlspecialchars($user['role']) ?></span></td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-card p-4">
            <h5 class="fw-bold mb-4 text-dark">Quick Actions</h5>
            <div class="d-grid gap-3">
                <a href="admin-users.php" class="btn btn-super-primary text-start p-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="fas fa-user-plus me-2"></i> Register New User</span>
                    <i class="fas fa-arrow-right small opacity-50"></i>
                </a>
                <a href="admin-events.php" class="btn btn-outline-dark text-start p-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="fas fa-calendar-plus me-2"></i> Create New Event</span>
                    <i class="fas fa-arrow-right small opacity-50"></i>
                </a>
                <a href="admin-settings.php" class="btn btn-outline-secondary text-start p-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="fas fa-cog me-2"></i> System Settings</span>
                    <i class="fas fa-arrow-right small opacity-50"></i>
                </a>
            </div>
        </div>
    </div>
</div>

</main> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>