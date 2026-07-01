<?php
// ሴሽኑ አስቀድሞ ካልተጀመረ ብቻ እንዲጀምር ማድረግ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'include/conn.php';

// Assuming you store the logged-in user's ID in the session
// If not, adjust $_SESSION['user_id'] to match your authentication system variables
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if session isn't found
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password     = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error_message = "አዲሱ የይለፍ ቃል እና ማረጋገጫው አይጣጣሙም! (New passwords do not match!)";
    } else {
        // Fetch current password hash from users table (adjust column/table names as per your DB setup)
        $query = "SELECT password FROM users WHERE id = $user_id LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify old password against hashed DB version
            if (password_verify($current_password, $user['password'])) {
                // Securely hash the new target password
                $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                $update_query = "UPDATE users SET password = '$new_hashed_password' WHERE id = $user_id";
                if (mysqli_query($conn, $update_query)) {
                    $success_message = "የይለፍ ቃልዎ በስኬት ተቀይሯል! (Password changed successfully!)";
                } else {
                    $error_message = "የመረጃ ቋት ስህተት ገጥሟል። እንደገና ይሞክሩ። (Database execution error.)";
                }
            } else {
                $error_message = "የአሁኑ የይለፍ ቃል የተሳሳተ ነው! (Current password is incorrect!)";
            }
        } else {
            $error_message = "ተጠቃሚው አልተገኘም። (User profile record missing.)";
        }
    }
}

include 'include/header.php';
include 'include/A_nav.php';
?>

<style>
    :root { 
        --sidebar-offset: 260px; 
    }
    
    body { 
        background-color: #f8fafc; 
        font-family: 'Inter', sans-serif;
    }
    
    .content-wrapper-custom {
        margin-left: var(--sidebar-offset) !important;
        padding: 40px !important;
        min-height: 100vh;
    }

    .settings-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
    }

    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
</style>

<main class="content-wrapper-custom">
    <div class="row mb-4">
        <div class="col-lg-8 col-xl-6">
            <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Account Settings</h2>
            <p class="text-muted mb-0 small">Update your security options and manage profile authentication credentials.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card settings-card border-0 shadow-sm p-4">
                <h5 class="fw-bold text-dark mb-4">
                    <i class="fas fa-key text-primary me-2" style="color: #4f46e5 !important;"></i>Change Password (የይለፍ ቃል መቀየር)
                </h5>

                <?php if ($success_message): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: 8px;">
                        <i class="fas fa-check-circle me-2"></i>
                        <div><?php echo $success_message; ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?php echo $error_message; ?></div>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" onsubmit="return validatePasswords();">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Current Password (የአሁኑ የይለፍ ቃል)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="current_password" class="form-control border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">New Password (አዲስ የይለፍ ቃል)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                            <input type="password" id="new_password" name="new_password" class="form-control border-start-0" placeholder="Minimum 6 characters" minlength="6" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Confirm New Password (አዲሱን የይለፍ ቃል ያረጋግጡ)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-check text-muted"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control border-start-0" placeholder="Repeat your new password" required>
                        </div>
                        <div id="passwordMismatchError" class="text-danger small mt-1 d-none">
                            <i class="fas fa-times-circle me-1"></i> Passwords do not match.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light rounded-pill px-4 small" style="font-weight: 500;">Cancel</button>
                        <button type="submit" name="change_password" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: #4f46e5; border-color: #4f46e5; font-weight: 600;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Swift client-side matching check before making resource requests to the server
function validatePasswords() {
    var newPass = document.getElementById('new_password').value;
    var confirmPass = document.getElementById('confirm_password').value;
    var errorDiv = document.getElementById('passwordMismatchError');

    if (newPass !== confirmPass) {
        errorDiv.classList.remove('d-none');
        return false;
    }
    errorDiv.classList.add('d-none');
    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>