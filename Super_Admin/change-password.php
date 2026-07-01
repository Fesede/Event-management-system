<?php
include 'include/conn.php';
session_start();

// Redirect if not logged in
/*if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}*/

$user_id = $_SESSION['user_id'];
$status_msg = "";

if (isset($_POST['change_password'])) {
    $current = $_POST['current_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    if ($new !== $confirm) {
        $status_msg = '<div class="alert alert-danger">New passwords do not match!</div>';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (password_verify($current, $user['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed, $user_id);
            $upd->execute();
            $status_msg = '<div class="alert alert-success">Password updated successfully.</div>';
        } else {
            $status_msg = '<div class="alert alert-danger">Current password is incorrect.</div>';
        }
    }
}

include 'include/header.php'; 

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="admin-users.php" class="text-decoration-none text-muted small fw-bold">
                    <i class="bi bi-arrow-left"></i> BACK TO USERS
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title fw-bold mb-1">Security Settings</h4>
                    <p class="text-muted small mb-4">Update your password to keep your account secure.</p>

                    <?php echo $status_msg; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Current Password</label>
                            <input type="password" name="current_pass" class="form-control form-control-lg bg-light" placeholder="••••••••" required>
                        </div>

                        <hr class="my-4 text-secondary opacity-25">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">New Password</label>
                            <input type="password" name="new_pass" class="form-control form-control-lg bg-light" placeholder="••••••••" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control form-control-lg bg-light" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            Save New Password
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted small">Forgot your old password? <a href="mailto:admin@example.com" class="text-primary">Contact Support</a></p>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa; /* Light gray background */
    }
    .card {
        border-radius: 12px;
    }
    .form-control:focus {
        background-color: #fff !important;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25 row rgba(13, 110, 253, 0.1);
    }
    .btn-primary {
        background-color: #0d6efd;
        border: none;
        padding: 12px;
    }
</style>

<?php include 'include/footer.php'; ?>