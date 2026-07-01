<?php 
// 1. DATABASE CONNECTION
include "include/conn.php";
$message = "";

// 2. PHP LOGIC (ALL-IN-ONE PROCESSING)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upload_dir = "../uploads/"; 
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    $form_type = $_POST['form_type'];

    // --- PROCESS EVENT ---
    if ($form_type == "event" && isset($_POST['submit_event'])) {
        $user_name   = $_POST['user_name'];
        $event_title = $_POST['event_title'];
        $category    = $_POST['category'];
        $event_date  = $_POST['event_date'];
        $event_time  = $_POST['event_time'];
        $location    = $_POST['location'];
        $event_link  = $_POST['event_link'];
        $event_desc  = $_POST['event_desc'];
        $status      = $_POST['status'];

        $new_name = "event_" . time() . "_" . $_FILES['event_image']['name'];
        
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_dir . $new_name)) {
            $stmt = $conn->prepare("INSERT INTO events (user_name, title, category, event_date, Time, Location, link, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $user_name, $event_title, $category, $event_date, $event_time, $location, $event_link, $event_desc, $new_name, $status);
            
            if($stmt->execute()) {
                header('Location: admin-events.php?msg=success');
                exit();
            } else {
                $message = "Event Error: " . $conn->error;
            }
        }
    }

    // --- PROCESS NEWS ---
    if ($form_type == "news" && isset($_POST['submit_news'])) {
        $news_author   = $_POST['news_author'];
        $news_title    = $_POST['news_title'];
        $news_category = $_POST['news_category'];
        $news_content  = $_POST['news_content'];
        $news_status   = $_POST['news_status'];

        $new_name = "news_" . time() . "_" . $_FILES['news_image']['name'];
        
        if (move_uploaded_file($_FILES['news_image']['tmp_name'], $upload_dir . $new_name)) {
            $stmt = $conn->prepare("INSERT INTO news (author, title, category, content, image, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $news_author, $news_title, $news_category, $news_content, $new_name, $news_status);
            
            if($stmt->execute()) {
                header('Location: admin-news.php?msg=success');
                exit();
            } else {
                $message = "News Error: " . $conn->error;
            }
        }
    }
}

// Shared Category Array
$categories = ['አባላት ጉዳይ ክፍል','ሕጻናት ንኡስ ክፍል','ማእከላውያን ንኡስ ክፍል','ወጣት ንኡስ ክፍል','ግቢ ጉባኤያት ንኡስ ክፍል','ትምህርት ዐቢይ ክፍል','ቤተ መጻሕፍት ንኡስ ክፍል','ምዝገባና መረጃ ክፍል','መዝሙር ንኡስ ክፍል','ኪነ ጥበብ ንኡስ ክፍል','የክሂሎት ማበልጸጊያ ንኡስ ክፍል','ጸሎትና መስተንግዶ ንኡስ ክፍል','የበጎ አድራጎት ንኡስ ክፍል','የገጠር ቤ/ክ ልማት ንኡስ ክፍል','ልማት ንኡስ ክፍል','ጉዞ ንኡስ ክፍል','ድጋፍና ማስተባበር ንኡስ ክፍል','ንዋይ አስተዳደር ዐቢይ ክፍል','የሰው ሀብት አስተዳደር ዐቢይ ክፍል','ጎልማሳ ክፍል','ምክር ክፍል','ጽህፈት ቤት','None'];

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    :root { --main-blue: #0d47a1; --bg-gray: #f4f7f6; }
    body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; }
    .form-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; margin-top: 30px; }
    .nav-pills .nav-link { color: #555; font-weight: 600; border-radius: 8px; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background-color: var(--main-blue) !important; color: #fff; }
    .img-preview { width: 100%; max-height: 200px; object-fit: contain; display: none; margin-bottom: 10px; border: 2px dashed #ddd; padding: 5px; border-radius: 10px; }
    .btn-submit { background: var(--main-blue); color: white; border: none; font-weight: bold; transition: 0.3s; padding: 12px; }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
</style>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="form-card">
                <h3 class="text-center fw-bold mb-4">Content Management System</h3>
                
                <?php if($message): ?>
                    <div class="alert alert-danger"><?php echo $message; ?></div>
                <?php endif; ?>

                <ul class="nav nav-pills nav-fill mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-event" type="button"><i class="fas fa-calendar-alt me-2"></i>Post Event</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-news" type="button"><i class="fas fa-newspaper me-2"></i>Post News</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    
                    <!-- 1. EVENT FORM SECTION -->
                    <div class="tab-pane fade show active" id="pills-event">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="event">
                            
                            <div class="text-center mb-4">
                                <img id="eventPreview" class="img-preview mx-auto">
                                <label class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-image me-1"></i> Upload Event Poster
                                    <input type="file" name="event_image" hidden accept="image/*" onchange="previewImg(this, 'eventPreview')" required>
                                </label>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="user_name" class="form-control" placeholder="Organizer Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Event Title</label>
                                    <input type="text" name="event_title" class="form-control" placeholder="Event Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Category (ክፍል)</label>
                                    <select name="category" class="form-select" required>
                                        <?php foreach($categories as $cat) echo "<option value='$cat'>$cat</option>"; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Date</label>
                                    <input type="date" name="event_date" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Time</label>
                                    <input type="time" name="event_time" class="form-control" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Location</label>
                                    <input type="text" name="location" class="form-control" placeholder="Where is it happening?" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="published">Published</option>
                                        <option value="draft" selected>Draft</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Registration Link (Optional)</label>
                                    <input type="url" name="event_link" class="form-control" placeholder="https://google.form/...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Description</label>
                                    <textarea name="event_desc" class="form-control" rows="3" placeholder="Tell us more about the event..."></textarea>
                                </div>
                            </div>
                            <button type="submit" name="submit_event" class="btn btn-submit w-100 mt-4">POST EVENT</button>
                        </form>
                    </div>

                    <!-- 2. NEWS FORM SECTION -->
                    <div class="tab-pane fade" id="pills-news">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="news">
                            
                            <div class="text-center mb-4">
                                <img id="newsPreview" class="img-preview mx-auto">
                                <label class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-camera me-1"></i> Upload News Image
                                    <input type="file" name="news_image" hidden accept="image/*" onchange="previewImg(this, 'newsPreview')" required>
                                </label>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Author</label>
                                    <input type="text" name="news_author" class="form-control" placeholder="Writer's Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Headline</label>
                                    <input type="text" name="news_title" class="form-control" placeholder="News Headline" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Category (ክፍል)</label>
                                    <select name="news_category" class="form-select" required>
                                        <?php foreach($categories as $cat) echo "<option value='$cat'>$cat</option>"; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Publishing Status</label>
                                    <select name="news_status" class="form-select" required>
                                        <option value="published">Published</option>
                                        <option value="draft" selected>Draft</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">News Content</label>
                                    <textarea name="news_content" class="form-control" rows="6" placeholder="Write full news article here..." required></textarea>
                                </div>
                            </div>
                            <button type="submit" name="submit_news" class="btn btn-submit w-100 mt-4" style="background: #28a745;">PUBLISH NEWS</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image Preview Script
function previewImg(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>