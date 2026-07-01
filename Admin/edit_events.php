<?php
include 'include/header.php';
include 'include/A_nav.php';
include 'include/conn.php'; 

// 1. Get the ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $query = "SELECT * FROM events WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        echo "<div class='error-msg'>Record not found.</div>";
        exit;
    }
}

// 2. Update Logic
if (isset($_POST['update_event'])) {
    $user_name   = mysqli_real_escape_string($conn, $_POST['user_name']);
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date  = mysqli_real_escape_string($conn, $_POST['event_date']);
    $link        = mysqli_real_escape_string($conn, $_POST['link']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location    = mysqli_real_escape_string($conn, $_POST['Location']);
    $time        = mysqli_real_escape_string($conn, $_POST['Time']);

    $update_sql = "UPDATE events SET 
                   user_name = '$user_name', 
                   title = '$title', 
                   event_date = '$event_date', 
                   link = '$link', 
                   description = '$description', 
                   Location = '$location', 
                   Time = '$time' 
                   WHERE id = $id";

    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Update Successful!'); window.location='admin-events.php';</script>";
    }
}
?>

<style>
    /* Custom Modern Styles */
    :root {
        --primary-color: #4f46e5;
        --bg-color: #f9fafb;
        --card-bg: #ffffff;
        --text-main: #1f2937;
        --border-color: #e5e7eb;
    }

    body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; }

    .edit-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .edit-card {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 30px;
        border: 1px solid var(--border-color);
    }

    .edit-header {
        border-bottom: 2px solid var(--bg-color);
        margin-bottom: 25px;
        padding-bottom: 15px;
    }

    .edit-header h2 { margin: 0; color: var(--text-main); font-size: 1.5rem; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .full-width { grid-column: span 2; }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #4b5563;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-group {
        margin-top: 30px;
        display: flex;
        gap: 12px;
    }

    .btn-save {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .btn-cancel {
        background-color: white;
        color: #374151;
        border: 1px solid var(--border-color);
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .btn-save:hover { opacity: 0.9; }
    .btn-cancel:hover { background-color: #f3f4f6; }

    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
    }
</style>

<div class="edit-container">
    <div class="edit-card">
        <div class="edit-header">
            <h2>Edit Event Details</h2>
        </div>

        <form action="" method="POST">
            <div class="form-grid">
                <!-- Row 1 -->
                <div class="form-group">
                    <label>User Name</label>
                    <input type="text" name="user_name" class="form-control" value="<?= htmlspecialchars($data['user_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title']) ?>" required>
                </div>

                <!-- Row 2 -->
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?= $data['event_date'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="Time" class="form-control" value="<?= $data['Time'] ?>" required>
                </div>

                <!-- Row 3 -->
                <div class="form-group full-width">
                    <label>Location</label>
                    <input type="text" name="Location" class="form-control" value="<?= htmlspecialchars($data['Location']) ?>" required>
                </div>

                <!-- Row 4 -->
                <div class="form-group full-width">
                    <label>Link (URL)</label>
                    <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($data['link']) ?>">
                </div>

                <!-- Row 5 -->
                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" name="update_event" class="btn-save">Save Changes</button>
                <a href="admin-events.php" class="btn-cancel">Back to List</a>
            </div>
        </form>
    </div>
</div>

<?php 
// include 'include/footer.php'; 
?>