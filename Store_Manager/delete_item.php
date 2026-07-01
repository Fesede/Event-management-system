<?php
session_start();
include 'include/conn.php';

// 🔒 Security Guard Layer: Block unauthorized external routing access
if (!isset($_SESSION['user_role']) || strtolower(trim($_SESSION['user_role'])) !== 'store manager') {
    header("Location: ../login.php"); 
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $delete_sql = "DELETE FROM inventory WHERE id = $id";
    
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: dashboard.php?status=deleted_success");
        exit();
    } else {
        header("Location: dashboard.php?status=deleted_failed");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>