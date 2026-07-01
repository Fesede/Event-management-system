<?php
// 1. Always start the session at the very top (Only Once!)
session_start();

include 'include/conn.php';

// If a user is already logged in, automatically redirect them to their respective panels
if (isset($_SESSION['user_role'])) {
    $current_role = strtolower(trim($_SESSION['user_role']));
    if ($current_role === 'admin') {
        header("Location: Admin/admin.php");
        exit();
    } elseif ($current_role === 'super admin') {
        header("Location: Super_Admin/admin.php");
        exit();
    } elseif ($current_role === 'store manager') {
        header("Location: Store_Manager/dashboard.php");
        exit();
    } elseif ($current_role === 'members manager' || $current_role === 'members') {
        header("Location: Members/dashboard.php");
        exit();
    }
}

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 🔒 1. FIXED CREDENTIALS FOR STORE MANAGER
    $fixed_manager_email = "manager@straguel.com";
    $fixed_manager_password = "straguel1995"; 

    // 🔒 2. FIXED CREDENTIALS FOR MEMBERS MANAGER
    $fixed_member_email = "members@straguel.com";
    $fixed_member_password = "members1995"; 

    // Check Store Manager bypass
    if ($email === $fixed_manager_email && $password === $fixed_manager_password) {
        $_SESSION['user_id'] = 'fixed_manager'; 
        $_SESSION['user_email'] = $fixed_manager_email;
        $_SESSION['user_role'] = 'store manager';

        header("Location: Store_Manager/dashboard.php");
        exit();
    }
    // Check Members Manager bypass
    elseif ($email === $fixed_member_email && $password === $fixed_member_password) {
        $_SESSION['user_id'] = 'fixed_members'; 
        $_SESSION['user_email'] = $fixed_member_email;
        $_SESSION['user_role'] = 'members manager';

        header("Location: Members/dashboard.php");
        exit();
    }
    else {
        // 🔄 DYNAMIC CHECK: Look up Admins and Super Admins from the database
        $sql = "SELECT id, email, password, role FROM users WHERE email = ?";
        $result = $conn->prepare($sql);
        $result->bind_param("s", $email);
        $result->execute();
        $result = $result->get_result();

        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                
                // Checks plain text or hashed configuration variants matching passwords
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_email'] = $row['email'];
                    $_SESSION['user_role'] = $row['role']; 

                    $userRole = strtolower(trim($row['role']));

                    // Route database-managed roles to their designated panels
                    if ($userRole === 'admin') {
                        header("Location: Admin/admin.php");
                        exit();
                    } elseif ($userRole === 'super admin') {
                        header("Location: Super_Admin/admin.php");
                        exit();
                    } elseif ($userRole === 'members manager' || $userRole === 'members') {
                        header("Location: Members/dashboard.php");
                        exit();
                    } else {
                        $error = "ይህንን ዳሽቦርድ ለመጠቀም ፈቃድ የለዎትም። የእርስዎ ሚና: '" . htmlspecialchars($row['role']) . "'";
                    }
                } else {
                    $error = "የተሳሳተ የኢሜል ወይም የይለፍ ቃል ፍንጭ አስገብተዋል።";
                }
            }
        } else {
            $error = "ይህ ኢሜል በስርዓታችን ውስጥ አልተገኘም::";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EventHub Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Inter', sans-serif; 
        }
        .login-card { 
            width: 100%; 
            max-width: 950px; 
            border: none; 
            border-radius: 24px; 
            overflow: hidden; 
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08); 
            background: white;
        }
        .login-image { 
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85), rgba(15, 23, 42, 0.95)), url('assets/image/head.png'); 
            background-size: cover; 
            background-position: center; 
            color: white; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            padding: 50px; 
        }
        .login-form-container { 
            padding: 60px; 
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background-color: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .btn-login { 
            padding: 14px; 
            border-radius: 12px; 
            font-weight: 600; 
            background: #2563eb; 
            border: none; 
            color: white; 
            transition: all 0.2s ease;
        }
        .btn-login:hover { 
            background: #1d4ed8; 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
            border-radius: 12px;
        }
        .back-link {
            color: #64748b;
            transition: color 0.2s ease;
        }
        .back-link:hover {
            color: #0f172a;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card login-card">
        <div class="row g-0">
            <div class="col-lg-5 d-none d-lg-flex login-image">
                <div>
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill small mb-4 fw-medium tracking-wide">MANAGEMENT PORTAL</span>
                    <h1 class="fw-bold mb-3 display-6">St.Raguel</h1>
                    <p class="text-white-50 lead fs-6">የክፍላቱን አውታር፣ መርሐ ግብሮችን እና ወቅታዊ ዜናዎችን በአንድ ማዕከል ይቆጣጠሩ።</p>
                </div>
                <div class="text-white-50 small">
                    &copy; 2026 St.Raguel. All rights reserved.
                </div>
            </div>
            
            <div class="col-lg-7 login-form-container">
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">እንኳን ደህና መጡ</h3>
                    <p class="text-muted small">እባክዎ መለያዎን ለመጠቀም የአስተዳዳሪ ምስክር ወረቀቶችን ያስገቡ።</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center small p-3 mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">የኢሜል አድራሻ</label>
                        <input type="email" name="email" class="form-control" placeholder="name@company.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-secondary mb-0">የይለፍ ቃል</label>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-login w-100 mb-4" name="login">ወደ ዳሽቦርድ ግባ</button>
                    
                    <div class="text-center">
                        <a href="index.php" class="text-decoration-none small back-link fw-medium">
                            <i class="fas fa-arrow-left me-2 small"></i>ወደ ዋናው ድረ-ገጽ ተመለስ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>