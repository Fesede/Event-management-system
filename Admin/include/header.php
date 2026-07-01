<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Administrative Authorization Guards
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'])) !== 'admin') {
    header("Location: ../login.php?error=unauthorized"); 
    exit();
}

$user_name = $_SESSION['user_name'] ?? ($_SESSION['user_email'] ?? 'Admin User');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
      :root {
        --sidebar-width: 260px;
        --header-height: 70px;
        --primary-accent: #4f46e5;
      }
      
      body {
        background-color: #f8fafc;
        color: #0f172a;
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
      }
      
      /* Unified White Top Nav Bar Framework */
      .admin-top-bar {
        background-color: #ffffff !important;
        height: var(--header-height);
        position: fixed;
        top: 0;
        right: 0;
        left: var(--sidebar-width);
        z-index: 1020;
        padding: 0 30px;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease-in-out;
      }

      /* Clean Profile Button System Custom Overrides */
      .profile-dropdown-trigger {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #334155 !important;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 500;
        transition: all 0.2s ease;
      }
      
      .profile-dropdown-trigger:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
      }

      #sidebarToggleBtn {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        padding: 10px 14px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
      }
      
      #sidebarToggleBtn:hover {
        background: #e2e8f0;
      }

      /* Desktop & Mobile Responsive Core Overrides */
      @media (max-width: 992px) {
        .admin-top-bar {
          left: 0 !important;
          padding: 0 15px;
        }
      }
    </style>
  </head>
  <body>

  <nav id="sidebar">
      <div class="p-4 sidebar-brand-block">
          <h4 class="fw-bold text-white mb-0">St.Raguel</h4>
          <small class="text-white-50">Management Portal</small>
      </div>
      
      <ul class="nav flex-column mb-auto px-2 mt-3">
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
              <a href="admin-notifications.php" class="nav-link <?= ($current_page == 'admin-notifications.php') ? 'active' : ''; ?>">
                  <i class="fas fa-bell me-2"></i> Notifications
              </a>
          </li>
          <li class="nav-item mb-1">
              <a href="setting.php" class="nav-link <?= ($current_page == 'setting.php') ? 'active' : ''; ?>">
                  <i class="fas fa-cog me-2"></i> Settings
              </a>
          </li>
      </ul>
      
      <div class="p-3 sidebar-footer-block mt-auto">
          <a href="../logout.php" class="btn btn-sidebar-logout w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="fas fa-sign-out-alt"></i> Logout
          </a>
      </div>
  </nav>
 
  <header class="admin-top-bar d-flex align-items-center">
    <div class="d-flex justify-content-between align-items-center w-100">
      
      <button id="sidebarToggleBtn" type="button" aria-label="Toggle Side Menu Visibility Grid">
        <i class="fas fa-bars"></i>
      </button>
 
      <div class="dropdown">
        <button class="btn profile-dropdown-trigger dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=4f46e5&color=fff" class="rounded-circle" width="26" height="26" alt="User Initial Graphic UI Avatar" />
          <span class="fw-semibold small text-truncate d-none d-sm-inline" style="max-width: 160px;"><?= htmlspecialchars($user_name) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-2" style="min-width: 200px;">
          <li><a class="dropdown-item py-2" href="profile.php"><i class="fas fa-user-cog me-2 text-muted"></i> Account Profile</a></li>
          <li><a class="dropdown-item py-2" href="change-password.php"><i class="fas fa-key me-2 text-muted"></i> Update Password</a></li>
          <li><hr class="dropdown-divider my-2" /></li>
          <li><a class="dropdown-item py-2 text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Close Portal Session</a></li>
        </ul>
      </div>
 
    </div>
  </header>