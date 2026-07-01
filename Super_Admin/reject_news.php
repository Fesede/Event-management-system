<?php
include "include/conn.php";

if (isset($_GET['id'])) {
    // 1. Sanitize the ID
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 2. Perform a "Soft Delete" by updating the status to 'rejected'
    // This makes the item disappear from public view without deleting it from the DB
    $sql = "UPDATE news SET status = 'rejected' WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the news list with a success message
        header("Location: admin-news.php?msg=rejected");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    die("No ID provided.");
}
?>