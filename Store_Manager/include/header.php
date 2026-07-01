<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Guard: Strict access control
if (!isset($_SESSION['user_role']) || strtolower(trim($_SESSION['user_role'])) !== 'store manager') {
    header("Location: ../login.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Store Manager | EventHub'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; overflow-x: hidden; }
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background-color: #0f172a; color: white; transition: all 0.3s; z-index: 1000; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; display: flex; align-items: center; border-radius: 8px; margin: 4px 15px; font-weight: 500; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #1e293b; color: white; }
        .sidebar .nav-link i { width: 25px; font-size: 1.1rem; }
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; transition: all 0.3s; }
        .top-navbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 15px 40px; margin-left: 260px; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); background: white; }
        @media (max-width: 991.98px) {
            .sidebar { margin-left: -260px; }
            .sidebar.active { margin-left: 0; }
            .main-content, .top-navbar { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column justify-content-between pb-3" id="sidebar">
    <div>
        <div class="p-4 border-bottom border-secondary border-opacity-25">
            <h5 class="fw-bold mb-0 text-white"><i class="fas fa-layer-group me-2 text-primary"></i>St.Raguel</h5>
            <small class="text-muted text-uppercase tracking-wider fs-7">Store Panel</small>
        </div>
        <ul class="nav flex-column mt-3">
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($active_tab) && $active_tab == 'dashboard') ? 'active' : ''; ?>" href="inventory.php">
            <i class="fas fa-chart-pie me-2"></i> ዳሽቦርድ (Dashboard)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($active_tab) && $active_tab == 'inventory') ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-boxes me-2"></i> የዕቃዎች ክምችት (Inventory)
        </a>
    </li>
</ul>
    </div>
    <div>
        <div class="px-3">
            <a href="../logout.php" class="btn btn-outline-danger w-100 rounded-pill py-2 small">
                <i class="fas fa-sign-out-alt me-1"></i> መውጫ (Logout)
            </a>
        </div>
    </div>
</div>

<div class="top-navbar d-flex justify-content-between align-items-center" id="topNav">
    <button class="btn btn-light d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="d-none d-sm-block">
        <span class="badge bg-slate text-dark bg-light px-3 py-2 border border-secondary border-opacity-10 rounded-pill">
            <i class="fas fa-calendar-alt text-primary me-1"></i> ዛሬ፡ <?php echo date('Y-m-d'); ?>
        </span>
    </div>
    <div class="d-flex align-items-center">
        <div class="text-end me-3">
            <p class="small fw-semibold text-dark mb-0">Store Manager</p>
            <p class="text-muted mb-0" style="font-size: 0.75rem;"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        </div>
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
            <?php echo strtoupper(substr($_SESSION['user_email'], 0, 1)); ?>
        </div>
    </div>
</div>

<div class="main-content" id="mainContent">