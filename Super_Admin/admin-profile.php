<?php
include "include/conn.php";
session_start();

// 1. Assume the user ID is stored in the session after login
// For testing, if no session exists, we'll use ID 1
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// --- 2. HANDLE UPDATE LOGIC ---
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $bio        = mysqli_real_escape_string($conn, $_POST['bio']);

    $update_query = "UPDATE users SET fname='$first_name', lname='$last_name', email='$email', bio='$bio' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating profile: " . mysqli_error($conn) . "</div>";
    }
}

// --- 3. FETCH CURRENT USER DATA ---
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Set variables with defaults if database fields are empty
$fname = htmlspecialchars($user['first_name']);
$lname = htmlspecialchars($user['last_name']);
$email = htmlspecialchars($user['email']);
$bio   = htmlspecialchars($user['bio'] ?? '');
$role  = htmlspecialchars($user['role']);
$photo = !empty($user['photo']) ? 'uploads/avatars/'.$user['photo'] : 'https://i.pravatar.cc/150?u=admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | EventHub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #1a1d20; color: white; }
        #main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .profile-cover { height: 160px; background: linear-gradient(to right, #1e3c72, #2a5298); border-radius: 15px 15px 0 0; }
        .profile-avatar-container { margin-top: -60px; padding-left: 30px; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; border: 5px solid #fff; object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>

    <?php 
    include 'include/header.php';
    include 'include/nav.php'; ?>

    <main id="main-content">
        <div class="container-fluid">
            
            <?php echo $message; ?>

            <!-- Profile Header Card -->
            <div class="card card-custom mb-4 overflow-hidden">
                <div class="profile-cover"></div>
                <div class="profile-avatar-container d-flex align-items-end mb-4">
                    <img src="<?php echo $photo; ?>" alt="Admin" class="profile-avatar">
                    <div class="ms-3 mb-2">
                        <h3 class="fw-bold mb-0"><?php echo $fname . " " . $lname; ?></h3>
                        <span class="badge bg-primary"><?php echo strtoupper($role); ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-custom p-4 mb-4">
                        <h5 class="fw-bold mb-4">Edit Personal Information</h5>
                        
                        <form action="" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo $fname; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo $lname; ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Short Bio</label>
                                    <textarea name="bio" class="form-control" rows="3"><?php echo $bio; ?></textarea>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update_profile" class="btn btn-primary px-4">Update Profile</button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Logic can be added here following same pattern -->
                </div>

                <div class="col-lg-4">
                    <div class="card card-custom p-4 mb-4 text-center">
                        <h6 class="text-muted fw-bold small text-uppercase">Account Stats</h6>
                        <div class="row mt-3">
                            <div class="col-6 border-end">
                                <h4 class="fw-bold mb-0">45</h4>
                                <small class="text-muted">Posts</small>
                            </div>
                            <div class="col-6">
                                <h4 class="fw-bold mb-0">12</h4>
                                <small class="text-muted">Events</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>