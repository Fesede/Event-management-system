<?php
include 'include/conn.php';

// 1. Get the ID and fetch existing data (including Time and Location)
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM `events` WHERE id='$id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) {
        die("Event not found.");
    }
} else {
    die("No ID provided.");
}

// 2. Handle the Update Logic
if (isset($_POST['update_event'])) {
    $user_name   = $_POST['user_name'];
    $event_title = $_POST['event_title'];
    $category    = $_POST['category'];
    $event_date  = $_POST['event_date'];
    $event_time  = $_POST['event_time']; // Added
    $location    = $_POST['location'];   // Added
    $event_link  = $_POST['event_link'];
    $event_desc  = $_POST['event_desc'];
    $status      = $_POST['status'];

    // Use Prepared Statements (Updated to include Time and Location)
    // The "sssssssi" becomes "ssssssssi" because we added two more strings
    $stmt = $conn->prepare("UPDATE `events` SET user_name=?, title=?, event_date=?, Time=?, Location=?, link=?, description=?, status=?, category=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $user_name, $event_title, $event_date, $event_time, $location, $event_link, $event_desc, $status, $category, $id);

    if ($stmt->execute()) {
        header('Location: admin-events.php?msg=updated');
        exit();
    } else {
        die("Update failed: " . $conn->error);
    }
}

// Category List Array
$categories = ['አባላት ጉዳይ ክፍል','ሕጻናት ንኡስ ክፍል','ማእከላውያን ንኡስ ክፍል','ወጣት ንኡስ ክፍል','ግቢ ጉባኤያት ንኡስ ክፍል','ትምህርት ዐቢይ ክፍል','ቤተ መጻሕፍት ንኡስ ክፍል','ምዝገባና መረጃ ክፍል','መዝሙር ንኡስ ክፍል','ኪነ ጥበብ ንኡስ ክፍል','የክሂሎት ማበልጸጊያ ንኡስ ክፍል','ጸሎትና መስተንግዶ ንኡስ ክፍል','የበጎ አድራጎት ንኡስ ክፍል','የገጠር ቤ/ክ ልማት ንኡስ ክፍል','ልማት ንኡስ ክፍል','ጉዞ ንኡስ ክፍል','ድጋፍና ማስተባበር ንኡስ ክፍል','ንዋይ አስተዳደር ዐቢይ ክፍል','የሰው ሀብት አስተዳደር ዐቢይ ክፍል','ጎልማሳ ክፍል','ምክር ክፍል','None'];

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    :root { --main-blue: #0d47a1; --bg-gray: #f4f7f6; }
    body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; }
    .form-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; margin-top: 30px; }
    .btn-submit { background: var(--main-blue); color: white; border: none; font-weight: bold; transition: 0.3s; }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); color: white; }
</style>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <h3 class="text-center fw-bold mb-4">Edit Event Content</h3>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="user_name" class="form-control" value="<?php echo htmlspecialchars($row['user_name']); ?>" required>
                        </div>
                        
                        <!-- Event Title -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Event Title</label>
                            <input type="text" name="event_title" class="form-control" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                        </div>
                        
                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category (ክፍል)</label>
                            <select name="category" class="form-select" required>
                                <?php 
                                    foreach($categories as $cat) {
                                        $selected = ($row['category'] == $cat) ? "selected" : "";
                                        echo "<option value='$cat' $selected>$cat</option>";
                                    } 
                                ?>
                            </select>
                        </div>
                        
                        <!-- Date -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?php echo $row['event_date']; ?>" required>
                        </div>

                        <!-- Time (NEW) -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Time</label>
                            <input type="time" name="event_time" class="form-control" value="<?php echo $row['Time']; ?>" required>
                        </div>

                        <!-- Location (NEW) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Location</label>
                            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($row['Location']); ?>" placeholder="Addis Ababa, Werda 9..." required>
                        </div>

                        <!-- Link -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Registration Link</label>
                            <input type="url" name="event_link" class="form-control" value="<?php echo htmlspecialchars($row['link']); ?>">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="event_desc" class="form-control" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select" required>
                               <option value='published' <?php if($row['status'] == 'published') echo 'selected'; ?>>Published</option>
                               <option value='draft' <?php if($row['status'] == 'draft') echo 'selected'; ?>>Draft</option>
                               <option value='rejected' <?php if($row['status'] == 'rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="update_event" class="btn btn-submit w-100 mt-4 py-3">Update Event</button>
                    <a href="admin-events.php" class="btn btn-light w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>