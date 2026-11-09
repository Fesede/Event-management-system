<?php
// 1. DATABASE CONNECTION
require_once 'include/user_admin_auth.php';
require_once 'include/conn.php';

// Check for valid user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin-users.php");
    exit();
}

$id = $_GET['id'];
$error_msg = "";

// Initialize structured defaults to prevent runtime array key exceptions
$user = [
    'first_name' => '',
    'category'   => 'None',
    'role'       => 'Editor',
    'status'     => 'inactive'
];

// Fetch matching user directly via secure parameterized lookup queries
$fetch_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$fetch_stmt->bind_param("s", $id);
$fetch_stmt->execute();
$query_result = $fetch_stmt->get_result();

if ($query_result && $query_result->num_rows > 0) {
    $user = $query_result->fetch_assoc();
} else {
    header("Location: admin-users.php");
    exit();
}

// 2. TRANSACTION UPDATE INTERCEPT LOGIC ENGINE
if (isset($_POST['update_user'])) {
    super_admin_validate_user_csrf();
    $fname  = trim($_POST['first_name']);
    $role   = $_POST['role'];
    $status = $_POST['status'];
    $cat    = $_POST['category']; // Reading the selected Category dropdown value

    $allowed_roles = ['Admin', 'Editor', 'Super Admin', 'Members Manager', 'Attendance Controller', 'Attendance Holder', 'Members'];
    if (!is_string($role) || !in_array($role, $allowed_roles, true)) {
        http_response_code(400);
        exit('Select a valid user role.');
    }

    // Double-check: Force 'None' values to look clean if database requires specific casing
    if (strtolower($cat) == 'none') {
        $cat = 'None'; 
    }

    // Explicitly updated column keys to match your exact configuration rules
    $stmt = $conn->prepare("UPDATE users SET first_name=?, role=?, status=?, category=? WHERE id=?");
    
    if ($stmt) {
        $stmt->bind_param("sssss", $fname, $role, $status, $cat, $id);
        
        if ($stmt->execute()) {
            // Success direction path bounce redirect
            header("Location: admin-users.php?msg=updated");
            exit();
        } else {
            // Captures runtime update failures dynamically if table columns mismatch
            $error_msg = "Database Update Failed: " . $stmt->error;
        }
    } else {
        $error_msg = "Database Preparation Failed: " . $conn->error;
    }
}

// Global Application Category Array Configuration Keys
$categories = ['አባላት ጉዳይ ክፍል','ሕጻናት ንኡስ ክፍል','ማእከላውያን ንኡስ ክፍል','ወጣት ንኡስ ክፍል','ግቢ ጉባኤያት ንኡስ ክፍል','ትምህርት ዐቢይ ክፍል','ቤተ መጻሕፍት ንኡስ ክፍል','ምዝገባና መረጃ ክፍል','መዝሙር ንኡስ ክፍል','ኪነ ጥበብ ንኡስ ክፍል','የክሂሎት ማበልጸጊያ ንኡስ ክፍል','ጸሎትና መስተንግዶ ንኡስ ክፍል','የበጎ አድራጎት ንኡስ ክፍል','የገጠር ቤ/ክ ልማት ንኡስ ክፍል','ልማት ንኡስ ክፍል','ጉዞ ንኡስ ክፍል','ድጋፍና ማስተባበር ንኡስ ክፍል','ንዋይ አስተዳደር ዐቢይ ክፍል','የሰው ሀብት አስተዳደር ዐቢይ ክፍል','ጎልማሳ ክፍል','ምክር ክፍል','ጽህፈት ቤት','None'];

include 'include/header.php'; 
include 'include/nav.php';
?>

<style>
    body { 
        background-color: #f8fafc; 
    }
    .edit-card-wrapper {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        width: 100%;
        max-width: 600px;
        padding: 30px;
        margin: 30px auto;
    }
</style>

<!-- 3. RENDER CONTENT INTERFACE PANELS -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            
            <div class="card edit-card-wrapper">
                <h4 class="fw-bold text-dark mb-4">
                    <i class="fas fa-user-edit text-primary me-2"></i>Update Profile: <?php echo htmlspecialchars($user['first_name'] ?? ''); ?>
                </h4>
                
                <!-- If there is a database column mismatch error, this box will print exactly why it failed -->
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger mb-3"><?php echo $error_msg; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(super_admin_user_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $cat_option): ?>
                                <!-- Clean checking system ensuring whitespace strings are removed cleanly during evaluation checks -->
                                <option value="<?php echo htmlspecialchars($cat_option); ?>" <?php echo (trim($user['category'] ?? '') == trim($cat_option)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">User Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin" <?php if (($user['role'] ?? '') == 'Admin') echo 'selected'; ?>>Admin</option>
                            <option value="Editor" <?php if (($user['role'] ?? '') == 'Editor') echo 'selected'; ?>>Editor</option>
                            <option value="Super Admin" <?php if (($user['role'] ?? '') == 'Super Admin') echo 'selected'; ?>>Super Admin</option>
                            <option value="Members Manager" <?php if (($user['role'] ?? '') == 'Members Manager') echo 'selected'; ?>>Members Manager</option>
                            <option value="Attendance Controller" <?php if (($user['role'] ?? '') == 'Attendance Controller') echo 'selected'; ?>>Attendance Controller</option>
                            <option value="Attendance Holder" <?php if (($user['role'] ?? '') == 'Attendance Holder') echo 'selected'; ?>>Attendance Holder</option>
                            <option value="Members" <?php if (($user['role'] ?? '') == 'Members') echo 'selected'; ?>>Members (read only)</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Account Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?php if (($user['status'] ?? '') == 'active') echo 'selected'; ?>>Active</option>
                            <option value="inactive" <?php if (($user['status'] ?? '') == 'inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="update_user" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #0d6efd; border-color: #0d6efd;">Save Changes</button>
                        <a href="admin-users.php" class="btn btn-light w-100 py-2 border text-muted text-decoration-none text-center small">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>