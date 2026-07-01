<?php
include "include/conn.php";

// Initialize variables to avoid "Undefined variable" warnings
$message = "";
$row = [];

// 1. FETCH EXISTING NEWS DATA
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM news WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        die("News item not found.");
    }
} else {
    die("No ID provided.");
}

// 2. UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_news'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $author   = mysqli_real_escape_string($conn, $_POST['news_author']);
    $title    = mysqli_real_escape_string($conn, $_POST['news_title']);
    $category = mysqli_real_escape_string($conn, $_POST['news_category']);
    $status   = mysqli_real_escape_string($conn, $_POST['news_status']);
    $content  = mysqli_real_escape_string($conn, $_POST['news_content']);
    
    // Handle Image Upload
    if (!empty($_FILES['news_image']['name'])) {
        $upload_dir = "../uploads/";
        $image_name = "news_" . time() . "_" . $_FILES['news_image']['name'];
        move_uploaded_file($_FILES['news_image']['tmp_name'], $upload_dir . $image_name);
        
        // Update with new image
        $update_sql = "UPDATE news SET author='$author', title='$title', category='$category', content='$content', image='$image_name', status='$status' WHERE id='$id'";
    } else {
        // Update without changing image
        $update_sql = "UPDATE news SET author='$author', title='$title', category='$category', content='$content', status='$status' WHERE id='$id'";
    }

    if (mysqli_query($conn, $update_sql)) {
        header("Location: admin-news.php?msg=updated");
        exit();
    } else {
        $message = "Update failed: " . mysqli_error($conn);
    }
}

// Shared Category Array
$categories = ['አባላት ጉዳይ ክፍል','ሕጻናት ንኡስ ክፍል','ማእከላውያን ንኡስ ክፍል','ወጣት ንኡስ ክፍል','ግቢ ጉባኤያት ንኡስ ክፍል','ትምህርት ዐቢይ ክፍል','ቤተ መጻሕፍት ንኡስ ክፍል','ምዝገባና መረጃ ክፍል','መዝሙር ንኡስ ክፍል','ኪነ ጥበብ ንኡስ ክፍል','የክሂሎት ማበልጸጊያ ንኡስ ክፍል','ጸሎትና መስተንግዶ ንኡስ ክፍል','የበጎ አድራጎት ንኡስ ክፍል','የገጠር ቤ/ክ ልማት ንኡስ ክፍል','ልማት ንኡስ ክፍል','ጉዞ ንኡስ ክፍል','ድጋፍና ማስተባበር ንኡስ ክፍል','ንዋይ አስተዳደር ዐቢይ ክፍል','የሰው ሀብት አስተዳደር ዐቢይ ክፍል','ጎልማሳ ክፍል','ምክር ክፍል','None'];

include 'include/header.php';
include 'include/nav.php';
?>

<style>
    :root { --main-blue: #0d47a1; --bg-gray: #f4f7f6; }
    body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; }
    .form-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; margin-top: 30px; }
    .img-preview-container { text-align: center; margin-bottom: 20px; }
    .img-preview { width: 100%; max-height: 200px; object-fit: contain; margin-bottom: 10px; border: 2px dashed #ddd; padding: 5px; border-radius: 10px; }
    .btn-submit { background: #28a745; color: white; border: none; font-weight: bold; transition: 0.3s; padding: 12px; }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
</style>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="form-card">
                <h3 class="text-center fw-bold mb-4">Edit News Content</h3>
                
                <?php if(!empty($message)): ?>
                    <div class="alert alert-danger"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="news">
                    
                    <div class="img-preview-container">
                        <!-- Show existing image by default -->
                        <img id="newsPreview" src="../uploads/<?php echo $row['image']; ?>" class="img-preview" style="display: block;">
                        <label class="btn btn-outline-success btn-sm">
                            <i class="fas fa-camera me-1"></i> Change News Image
                            <input type="file" name="news_image" hidden accept="image/*" onchange="previewImg(this, 'newsPreview')">
                        </label>
                        <p class="small text-muted mt-1">Leave empty to keep current image</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Author</label>
                            <input type="text" name="news_author" class="form-control" value="<?php echo htmlspecialchars($row['author']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Headline</label>
                            <input type="text" name="news_title" class="form-control" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category (ክፍል)</label>
                            <select name="news_category" class="form-select" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($row['category'] == $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Publishing Status</label>
                            <select name="news_status" class="form-select" required>
                                <option value="published" <?php echo ($row['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($row['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="rejected" <?php echo ($row['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold">News Content</label>
                            <textarea name="news_content" class="form-control" rows="6" required><?php echo htmlspecialchars($row['content']); ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_news" class="btn btn-submit w-100 mt-4">UPDATE NEWS</button>
                    <a href="admin-news.php" class="btn btn-light w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImg(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById(previewId);
            img.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>