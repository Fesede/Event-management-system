<?php
require_once 'include/user_admin_auth.php';
require_once 'include/conn.php';

// --- 2. አዲስ ተጠቃሚ የመመዝገብ ሎጅክ (Add User Logic) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    super_admin_validate_user_csrf();
    $allowed_roles = ['Admin', 'Editor', 'Super Admin', 'Members Manager', 'Attendance Controller', 'Attendance Holder', 'Members'];
    $submitted_role = $_POST['role'] ?? '';
    if (!is_string($submitted_role) || !in_array($submitted_role, $allowed_roles, true)) {
        http_response_code(400);
        exit('Select a valid user role.');
    }

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $category   = mysqli_real_escape_string($conn, $_POST['category']);
    $role       = mysqli_real_escape_string($conn, $submitted_role);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // የፎቶ መጫኛ ማስተናገጃ (Photo Upload)
    $photo_name = ""; 
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = time() . "_" . basename($_FILES['photo']['name']);
        
        $target_dir = "../uploads/avatars/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo_name);
    }

    $sql = "INSERT INTO users (first_name, last_name, email, password, role, category, photo, status) 
            VALUES ('$first_name', '$last_name', '$email', '$password', '$role', '$category', '$photo_name', 'active')";
    
    if (mysqli_query($conn, $sql)) {
        header('Location: admin-users.php');
        exit();
    }
}

// --- 3. ሁሉንም ተጠቃሚዎች ከዳታቤዝ ማውጣት (Fetch All Users) ---
$fetch_all_users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

$categories = ['አባላት ጉዳይ ክፍል','ሕጻናት ንኡስ ክፍል','ማእከላውያን ንኡስ ክፍል','ወጣት ንኡስ ክፍል','ግቢ ጉባኤያት ንኡስ ክፍል','ትምህርት ዐቢይ ክፍል','ቤተ መጻሕፍት ንኡስ ክፍል','ምዝገባና መረጃ ክፍል','መዝሙር ንኡስ ክፍል','ኪነ ጥበብ ንኡስ ክፍል','የክሂሎት ማበልጸጊያ ንኡስ ክፍል','ጸሎትና መስተንግዶ ንኡስ ክፍል','የበጎ አድራጎት ንኡስ ክፍል','የገጠር ቤ/ክ ልማት ንኡስ ክፍል','ልማት ንኡስ ክፍል','ጉዞ ንኡስ ክፍል','ድጋፍና ማስተባበር ንኡስ ክፍል','ንዋይ አስተዳደር ዐቢይ ክፍል','የሰው ሀብት አስተዳደር ዐቢይ ክፍል','ጎልማሳ ክፍል','ምክር ክፍል','ጽህፈት ቤት','None'];

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    body { background-color: #f8fafc; }
    
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    /* 💡 ክፍተቱን የሚያጠፋው እና ሰንጠረዡን ከሌሎቹ ገጾች ጋር ፍጹም አንድ አይነት የሚያደርገው ስታይል */
    .card-table-wrapper {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin: 0 !important; /* የውጭ ክፍተት እንዳይኖር */
    }

    .table thead th {
        background: #fdfdfd;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        color: #888;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }

    .btn-add {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
    }
</style>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">User Management</h2>
            <p class="text-muted mb-0">Control team access and category permissions.</p>
        </div>
        <button type="button" class="btn btn-primary btn-add shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal" style="background-color: #0d6efd; border-color: #0d6efd;">
            <i class="fas fa-plus-circle me-2"></i> Add New User
        </button>
    </div>
</div>

<div class="card card-table-wrapper p-2">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 border-0">Team Member</th>
                    <th class="border-0">Email Address</th>
                    <th class="border-0">Department / Category</th>
                    <th class="border-0">Role</th>
                    <th class="border-0">Status</th>
                    <th class="text-end pe-4 border-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fetch_all_users_result && mysqli_num_rows($fetch_all_users_result) > 0): ?>
                    <?php while($user = mysqli_fetch_assoc($fetch_all_users_result)): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <?php 
                                    $avatar_file = '../uploads/avatars/' . $user['photo'];
                                    if (!empty($user['photo']) && file_exists($avatar_file)) {
                                        $img = $avatar_file;
                                    } else {
                                        $img = 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . ' ' . $user['last_name']) . '&background=random';
                                    }
                                ?>
                                <img src="<?php echo $img; ?>" class="user-avatar me-3" alt="Avatar">
                                <div>
                                    <span class="fw-bold d-block text-dark small"><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></span>
                                    <small class="text-muted" style="font-size: 11px;">Ref: #EVH-0<?php echo $user['id']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge bg-light text-dark fw-medium border px-2 py-1"><?php echo htmlspecialchars($user['category']); ?></span></td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 fw-bold text-uppercase" style="font-size: 11px; color:white;">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'danger'; ?> px-3 py-1 rounded-pill small fw-bold text-uppercase" style="font-size: 11px;">
                                <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group gap-1">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-light btn-sm border" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                                <a href="reject_user.php?id=<?php echo $user['id']; ?>" class="btn btn-light btn-sm border" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                    <i class="fas fa-trash text-danger"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main> <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" id="addUserModalLabel">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(super_admin_user_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">First Name</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Abebe" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Kebede" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach($categories as $cat) echo "<option value='$cat'>$cat</option>"; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="example@eventhub.et" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">User Role</label>
                            <select name="role" class="form-select">
                                <option value="Editor">Editor</option>
                                <option value="Admin">Admin</option>
                                <option value="Super Admin">Super Admin</option>
                                <option value="Members Manager">Members Manager</option>
                                <option value="Attendance Controller">Attendance Controller</option>
                                <option value="Attendance Holder">Attendance Holder</option>
                                <option value="Members">Members (read only)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Profile Photo</label>
                            <input type="file" name="photo" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_user" class="btn btn-primary w-100 py-2 fw-bold">CREATE ACCOUNT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>