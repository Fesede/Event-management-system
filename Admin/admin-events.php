<?php
include 'include/header.php';
include 'include/A_nav.php';
include 'include/conn.php';

// Start session to identify the logged-in user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure you have a fallback if the session isn't set yet
$current_user = $_SESSION['user_name'] ?? 'root'; 

// 1. Fetch "My Events"
$my_query = "SELECT * FROM events WHERE user_name = '$current_user' ORDER BY created_at DESC";
$my_events = mysqli_query($conn, $my_query);

// 2. Fetch "Community Events" (Others)
$other_query = "SELECT * FROM events WHERE user_name != '$current_user' AND status = 'published' ORDER BY created_at DESC";
$other_events = mysqli_query($conn, $other_query);
?>

<style>
    :root {
        --primary-color: #4f46e5;
        --sidebar-width: 260px; /* Adjusted to match your sidebar in the screenshot */
        --bg-body: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
    }

    body {
        background-color: var(--bg-body);
        font-family: 'Inter', sans-serif;
        margin: 0;
    }

    /* FIX: This pushes content to the right of your sidebar */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 40px;
        min-height: 100vh;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .section-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .count-badge {
        background: var(--primary-color);
        color: white;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .table-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 40px;
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #fcfcfd;
        text-align: left;
        padding: 16px;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    tr:last-child td { border-bottom: none; }

    .status-pill {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-published { background: #dcfce7; color: #166534; }
    .status-draft { background: #fef3c7; color: #92400e; }

    .action-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .action-link:hover { text-decoration: underline; }

    .empty-msg {
        padding: 40px;
        text-align: center;
        color: var(--text-muted);
        font-style: italic;
    }
    .btn-add-new {
    background-color: #4f46e5;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: background 0.2s;
}
.btn-add-new:hover { background-color: #4338ca; }
</style>

<div class="main-content">
    
    <!-- My Personal Events Section -->
    <div class="section-header">
        <h2>My Personal Events</h2>
        <span class="count-badge"><?= mysqli_num_rows($my_events) ?></span>
    </div>
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <a href="add_event.php" class="btn-add-new">+ Add New Event</a>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($my_events) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($my_events)): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                        <td><?= $row['event_date'] ?> at <?= $row['Time'] ?></td>
                        <td><?= htmlspecialchars($row['Location']) ?></td>
                        <td>
                            <span class="status-pill status-<?= $row['status'] ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_events.php?id=<?= $row['id'] ?>" class="action-link">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-msg">You haven't posted any events yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Community Events Section -->
    <div class="section-header">
        <h2>Community Events</h2>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Posted By</th>
                    <th>Event Title</th>
                    <th>Location</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($other_events) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($other_events)): ?>
                    <tr>
                        <td><span style="color: var(--text-muted)">@</span><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                        <td><?= htmlspecialchars($row['Location']) ?></td>
                        <td><?= $row['Time'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-msg">No community events to show.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
