<?php
include 'include/conn.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Update status to inactive instead of deleting
    $sql = "UPDATE users SET status = 'inactive' WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-users.php?msg=rejected");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>