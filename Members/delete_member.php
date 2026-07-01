<?php
session_start();
include 'includes/connect.php';

// 🔒 Security Layer: Deny unauthenticated routing calls straight to processing controllers
if (!isset($_SESSION['user_role'])) {
    header("Location: ../login.php"); 
    exit();
}

// 🗑️ Handle explicit targeting parameter records cleanup routines
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $delete_sql = "DELETE FROM members WHERE id = $id";
    
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: members.php?status=deleted_success");
        exit();
    } else {
        header("Location: members.php?status=deleted_failed");
        exit();
    }
} else {
    header("Location: members.php");
    exit();
}
?>