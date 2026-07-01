<?php
include 'include/header.php';
include 'include/A_nav.php';
include 'include/conn.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// AUTO-ASSIGN: Taking Author and Category directly from the login session
$author_name = $_SESSION['user_name'] ?? 'Admin User'; 
$user_category = $_SESSION['user_role'] ?? 'General'; // Assuming 'user_role' determines the category

if (isset($_POST['submit_event'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date  = mysqli_real_escape_string($conn, $_POST['event_date']);
    $time        = mysqli_real_escape_string($conn, $_POST['Time']);
    $location    = mysqli_real_escape_string($conn, $_POST['Location']);
    $link        = mysqli_real_escape_string($conn, $_POST['link']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Status defaults to 'draft' per your schema; image is a placeholder
    $sql = "INSERT INTO events (user_name, title, event_date, Time, Location, category, link, description, status, image) 
            VALUES ('$author_name', '$title', '$event_date', '$time', '$location', '$user_category', '$link', '$description', 'draft', 'default.jpg')";

    if (mysqli_query($conn, $sql)) {
        header('location: admin-events.php');
    }
}
?>

<style>
    :root {
        --primary: #4f46e5;
        --sidebar-width: 260px;
    }

    /* Fixed alignment for Screenshot 2026-05-13 205319.png */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 40px;
        background-color: #f8fafc;
        min-height: 100vh;
    }

    .form-card {
        background: white;
        max-width: 750px;
        margin: 0 auto;
        padding: 30px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .auto-filled-bar {
        background: #f1f5f9;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: #64748b;
        border: 1px dashed #cbd5e1;
    }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full { grid-column: span 2; }

    label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 0.9rem; }
    
    .form-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-sizing: border-box;
    }

    .btn-save {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div class="main-content">
    <div class="form-card">
        <h2 style="margin-bottom: 10px;">Create Event</h2>
        <p style="color: #64748b; margin-bottom: 25px;">Fill in the details below to add a new event to the church calendar.</p>
        
        <!-- AUTO INFO BOX -->
        <div class="auto-filled-bar">
            <span><strong>Author:</strong> <?= htmlspecialchars($author_name) ?></span>
            <span><strong>Category:</strong> <?= htmlspecialchars($user_category) ?></span>
        </div>

        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Event Title</label>
                    <input type="text" name="title" class="form-input" required placeholder="e.g. Weekly Prayer Meeting">
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="event_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="Time" class="form-input" required>
                </div>

                <div class="form-group full">
                    <label>Location</label>
                    <input type="text" name="Location" class="form-input" required placeholder="Addis Ababa, St. Raguel Hall">
                </div>

                <div class="form-group full">
                    <label>Website/Registration Link (Optional)</label>
                    <input type="url" name="link" class="form-input" placeholder="https://example.com">
                </div>

                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" class="form-input" rows="5" placeholder="Describe the event details..."></textarea>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px; align-items: center;">
                <button type="submit" name="submit_event" class="btn-save">Publish as Draft</button>
                <a href="admin-events.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>

