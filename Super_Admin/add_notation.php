<?php
include "include/conn.php";

session_start();
// Security Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: ../login.php?error=unauthorized");
    exit();
}
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_notation'])) {
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $msg     = mysqli_real_escape_string($conn, $_POST['message']);
    $type    = mysqli_real_escape_string($conn, $_POST['type']);
    $icon    = mysqli_real_escape_string($conn, $_POST['icon']);

    $sql = "INSERT INTO notifications (title, message, type, icon, status) VALUES ('$title', '$msg', '$type', '$icon', 'unread')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-notifications.php?msg=success"); exit();
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

include 'include/header.php';
include 'include/nav.php';
?>

<div id="main-content" style="margin-left: 250px; padding: 40px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <h3 class="fw-bold mb-4">Create New Notation</h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Notation Title</label>
                                <input type="text" name="title" class="form-control form-control-lg" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Alert Style</label>
                                    <select name="type" class="form-select">
                                        <option value="primary text-primary">Info (Blue)</option>
                                        <option value="success text-success">Success (Green)</option>
                                        <option value="warning text-warning">Warning (Yellow)</option>
                                        <option value="danger text-danger">Urgent (Red)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Icon</label>
                                    <select name="icon" class="form-select">
                                        <option value="fa-bell">Bell</option>
                                        <option value="fa-bullhorn">Announcement</option>
                                        <option value="fa-exclamation-circle">Warning</option>
                                        <option value="fa-info-circle">Information</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Message Content</label>
                                <textarea name="message" class="form-control" rows="6" required></textarea>
                            </div>
                            <button type="submit" name="submit_notation" class="btn btn-primary w-100 py-3 fw-bold">PUBLISH NOTATION</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>