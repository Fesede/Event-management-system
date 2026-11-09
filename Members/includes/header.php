<?php
// Check if a session is already active before trying to start one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/attendance.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: ../login.php");
    exit();
}

attendance_require_roles(['super admin', 'members manager', 'attendance controller', 'attendance holder', 'members']);

$can_manage_members = in_array($attendance_role, ['super admin', 'members manager'], true);

// Set fallback title strings if not predefined on the main rendering template page
$page_title = isset($page_title) ? $page_title : "Members Management System";
$active_tab = isset($active_tab) ? $active_tab : "dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --dark-navy: #0f172a;
            --light-bg: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            overflow-x: hidden;
        }

        /* Fixed Sidebar Navigation Panel styling rules */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--dark-navy);
            color: #94a3b8;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        }

        .sidebar-brand {
            padding: 24px;
            font-size: 1.25rem;
            color: white;
            border-bottom: 1px solid #1e293b;
        }

        .sidebar-menu {
            padding: 20px 14px;
            list-style: none;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            background-color: #1e293b;
            color: white;
        }

        .sidebar-link i {
            width: 24px;
            font-size: 1.1rem;
        }

        /* Setting custom dashboard active selection tab states */
        .sidebar-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* Top Modular Interactive Core Navbar Structural Styling Rules */
        .main-navbar {
            height: 70px;
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            padding: 0 30px;
        }

        /* Principal Workspace Wrapper layout parameters wrapper */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 100px 30px 40px 30px;
            min-height: 100vh;
        }

        .card-custom {
            border-radius: 16px;
            background-color: white;
            border: 1px solid #e2e8f0;
        }

        /* Handle responsive scaling break targets dynamically for smaller screens */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-navbar {
                left: 0;
                padding: 0 15px;
            }
            .main-content {
                margin-left: 0;
                padding: 95px 15px 30px 15px;
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebarMenu">
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <span class="fw-bold tracking-wide"><i class="fas fa-church text-primary me-2"></i>St. Raguel</span>
            <button class="btn btn-sm btn-dark d-lg-none border-0" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link <?php echo ($active_tab === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie me-2"></i> ዳሽቦርድ (Dashboard)
                </a>
            </li>
            <?php if (!in_array($attendance_role, ['attendance holder', 'attendance controller'], true)): ?>
            <li class="sidebar-item">
                <a href="members.php" class="sidebar-link <?php echo ($active_tab === 'members') ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> የአባላት ዝርዝር (Members)
                </a>
            </li>
            <?php endif; ?>
            <?php if ($attendance_role !== 'members'): ?>
            <li class="sidebar-item">
                <a href="attendance.php" class="sidebar-link <?php echo ($active_tab === 'attendance') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check me-2"></i> Attendance
                </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-item mt-4 pt-3 border-top border-secondary border-opacity-20">
                <a href="../logout.php" class="sidebar-link text-danger" >
                    <i class="fas fa-sign-out-alt me-2"></i> ከሲስተሙ ውጣ (Logout)
                </a>
            </li>
        </ul>
    </div>

    <nav class="main-navbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none border me-3 rounded-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold small uppercase d-none d-sm-inline-block">
                <i class="fas fa-shield-halved me-1"></i> MEMBERS CONTROL CENTER
            </span>
        </div>
        
        <div class="dropdown">
            <div class="d-flex align-items-center dropdown-toggle cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="text-end me-2 d-none d-sm-block">
                    <h6 class="fw-bold mb-0 text-dark small"><?php echo htmlspecialchars($_SESSION['user_email']); ?></h6>
                    <small class="text-muted text-capitalize fs-7" style="font-size: 0.75rem;"><?php echo htmlspecialchars($_SESSION['user_role']); ?></small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 0.95rem;">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 p-2 mt-2">
                <li><span class="dropdown-item-text text-muted small pb-2 border-bottom mb-1">መለያ መቆጣጠሪያ</span></li>
                <li><a class="dropdown-item rounded-2 text-danger small py-2" href="../logout.php"><i class="fas fa-sign-out-alt me-2 small"></i>ውጣ (Logout)</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
    <script>
        function toggleSidebar() {
            document.getElementById('sidebarMenu').classList.toggle('show');
        }
    </script>