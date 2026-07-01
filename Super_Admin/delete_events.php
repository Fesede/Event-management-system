<?php
include 'include/conn.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Instead of deleting, we update the status to 'rejected'
    $sql = "UPDATE `events` SET status='rejected' WHERE id='$id'";
    
    if (mysqli_query($conn, $sql)) {
        // Redirect back with a success message
        header("Location: admin-events.php?msg=rejected");
        exit();
    } else {
        die("Error updating status: " . mysqli_error($conn));
    }
} else {
    header("Location: admin-events.php");
    exit();
}
?>