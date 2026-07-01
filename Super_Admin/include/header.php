<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'include/conn.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'])) !== 'super admin') {
    header("Location: ../login.php?error=unauthorized"); 
    exit();
}

$sql = "SELECT * FROM `users` WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$db_user = $stmt->get_result()->fetch_assoc();

$user_name = $db_user['first_name'] ?? ($_SESSION['user_name'] ?? ($_SESSION['user_email'] ?? 'Super Admin'));
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Admin Panel | St.Raguel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    
    <style>
        :root {
          --sidebar-width: 260px;
          --header-height: 70px;
          --primary-color: #be123c; 
          --bg-dark-slate: #1e2530; 
          --text-main: #1e293b;
          --bg-light: #f8fafc;
        }

        body {
          background-color: var(--bg-light);
          color: var(--text-main);
          font-family: 'Inter', sans-serif;
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        .super-admin-header {
          background: var(--bg-dark-slate) !important; 
          padding: 0 30px !important;
          height: var(--header-height) !important;
          border-bottom: 1px solid #2d3748 !important;
          position: fixed !important;
          top: 0 !important;
          right: 0 !important;
          left: var(--sidebar-width) !important; 
          width: calc(100% - var(--sidebar-width)) !important; 
          z-index: 999 !important; 
          box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
          display: flex !important;
          justify-content: space-between !important;
          align-items: center !important;
        }

        .super-main-wrapper {
          margin-left: var(--sidebar-width) !important; 
          padding-top: calc(var(--header-height) + 25px) !important; 
          padding-left: 30px !important;
          padding-right: 30px !important;
          padding-bottom: 40px !important;
          position: relative !important;
          z-index: 1 !important;
          min-height: 100vh;
        }

        #sidebar {
          width: var(--sidebar-width) !important;
          min-width: var(--sidebar-width) !important;
          max-width: var(--sidebar-width) !important;
          background: var(--bg-dark-slate) !important; 
          color: #ffffff !important;
          height: 100vh !important;
          position: fixed !important; 
          left: 0 !important;
          top: 0 !important;
          z-index: 1000 !important; 
          border-right: 1px solid #2d3748 !important;
          margin: 0 !important; 
          padding: 0 !important;
          display: flex;
          flex-direction: column;
        }

        .brand-wrapper {
          border-bottom: 1px solid #2d3748;
          height: var(--header-height); 
          display: flex;
          align-items: center;
          padding: 0 20px;
        }

        #sidebar .nav-link {
          color: #a0aec0;
          padding: 12px 15px;
          border-radius: 8px;
          font-weight: 500;
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          text-decoration: none;
        }

        #sidebar .nav-link i {
          width: 25px;
          font-size: 1.1rem;
        }

        #sidebar .nav-link:hover, 
        #sidebar .nav-link.active {
          color: #ffffff !important;
          background: var(--primary-color) !important; 
        }

        .header-profile-dropdown {
          position: relative !important;
        }

        .profile-btn {
          background: rgba(255, 255, 255, 0.1) !important;
          border: 1px solid rgba(255, 255, 255, 0.15) !important;
          padding: 6px 16px !important;
          border-radius: 10px !important;
          color: #ffffff !important;
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .dropdown-menu {
          border-radius: 12px;
          padding: 8px;
          min-width: 210px;
          border: 1px solid #e2e8f0;
          box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
          z-index: 1050 !important;
        }

        .dropdown-item {
          border-radius: 8px;
          transition: 0.2s;
        }

        .dropdown-item:hover {
          background-color: #f1f5f9;
          color: var(--primary-color);
        }

        .btn-logout {
          background: rgba(239, 68, 68, 0.1);
          color: #ef4444;
          border: 1px solid transparent;
          padding: 10px;
          border-radius: 8px;
          font-weight: 600;
          text-decoration: none;
          transition: 0.2s;
        }

        .btn-logout:hover {
          background: #ef4444;
          color: #ffffff;
        }

        @media (max-width: 768px) {
          .super-admin-header {
              left: 0 !important; 
              width: 100% !important;
              padding: 0 15px !important;
          }

          .super-main-wrapper {
              margin-left: 0 !important; 
              padding-left: 15px !important;
              padding-right: 15px !important;
              padding-top: calc(var(--header-height) + 20px) !important;
          }

          #sidebar {
              transform: translateX(-100%) !important; 
              transition: transform 0.3s ease !important;
          }
          
          #sidebar.show {
              transform: translateX(0) !important; 
          }

          #sidebarOverlay {
              position: fixed;
              top: 0;
              left: 0;
              width: 100vw;
              height: 100vh;
              background: rgba(0, 0, 0, 0.5);
              z-index: 999;
              display: none;
          }

          #sidebarOverlay.show {
              display: block;
          }
        }
    </style>
</head>
<body>

<header class="super-admin-header text-white">
  <div class="d-flex align-items-center">
    <button id="sidebarToggle" class="btn btn-outline-light d-md-none border-0 fs-4 p-1 me-2" type="button">
        <i class="fas fa-bars"></i>
    </button>
    <div class="d-none d-md-block">
        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 1.1rem;">
          <i class="fas fa-user-shield text-danger"></i> Super Admin Portal
        </h5>
        <small class="text-white-50" style="font-size: 0.8rem;">St.Raguel Church Management</small>
    </div>
  </div>

  <div class="d-md-none text-center">
      <h6 class="fw-bold mb-0" style="font-size: 1rem;">St.Raguel</h6>
  </div>

  <div class="dropdown header-profile-dropdown">
    <button class="btn profile-btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background-color: #be123c; color: #fff; font-size: 0.85rem;">
          <i class="fas fa-user"></i>
      </div>
      <span class="small fw-bold"><?= htmlspecialchars($user_name) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
      <li><a class="dropdown-item py-2" href="admin-profile.php"><i class="fas fa-user-cog me-2 text-muted"></i> Profile</a></li>
      <li><a class="dropdown-item py-2" href="change-password.php"><i class="fas fa-key me-2 text-muted"></i> Change Password</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger py-2" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
    </ul>
  </div>
</header>

<nav id="sidebar" class="d-flex flex-column">
    <div class="p-4 brand-wrapper d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-white mb-0" style="letter-spacing: 0.5px;">St.Raguel</h4>
            <small class="text-danger fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px; text-transform: uppercase;">
                <i class="fas fa-user-shield me-1"></i> Super Admin Portal
            </small>
        </div>
        <button id="sidebarClose" class="btn text-white d-md-none border-0 p-0 fs-4" type="button">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <ul class="nav flex-column mb-auto px-2 mt-3" style="list-style: none; padding-left: 0;">
        <li class="nav-item mb-1">
            <a href="admin.php" class="nav-link <?= ($current_page == 'admin.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="admin-events.php" class="nav-link <?= ($current_page == 'admin-events.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-plus me-2"></i> Events
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="admin-news.php" class="nav-link <?= ($current_page == 'admin-news.php') ? 'active' : ''; ?>">
                <i class="fas fa-newspaper me-2"></i> News
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="admin-users.php" class="nav-link <?= ($current_page == 'active' && $current_page == 'admin-users.php') ? 'active' : ''; ?>">
                <i class="fas fa-users me-2"></i> Users
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="admin-notifications.php" class="nav-link <?= ($current_page == 'admin-notifications.php') ? 'active' : ''; ?>">
                <i class="fas fa-bell me-2"></i> Notifications
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="admin-settings.php" class="nav-link <?= ($current_page == 'admin-settings.php') ? 'active' : ''; ?>">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </li>
    </ul>
    
    <div class="p-3 border-top border-secondary-subtle">
        <a href="../logout.php" class="btn btn-logout w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<div id="sidebarOverlay" class="d-md-none"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    const closeBtn = document.getElementById("sidebarClose");
    const overlay = document.getElementById("sidebarOverlay");

    if(toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            sidebar.classList.add("show");
            overlay.classList.add("show");
        });
    }
    if(closeBtn && sidebar && overlay) {
        closeBtn.addEventListener("click", function (e) {
            e.preventDefault();
            sidebar.classList.remove("show");
            overlay.classList.remove("show");
        });
    }
    if(overlay && sidebar) {
        overlay.addEventListener("click", function () {
            sidebar.classList.remove("show");
            overlay.classList.remove("show");
        });
    }
});
</script>

<main class="super-main-wrapper">