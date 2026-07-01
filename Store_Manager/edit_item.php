<?php 
$page_title = "Edit Inventory Item";
$active_tab = "inventory"; 

// 1. Include modular layout structural blocks and connection setup
include 'include/header.php'; 
include 'include/conn.php'; 

$error_msg = "";
$success_msg = "";

// 🔍 Check if ID parameters are passed via the URL context safely
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

// 2. Fetch the target item's existing data snapshot records
$fetch_res = mysqli_query($conn, "SELECT * FROM inventory WHERE id = $id");
$item = mysqli_fetch_assoc($fetch_res);

// Redirect out if no matching records exist in the database table
if (!$item) {
    header("Location: dashboard.php");
    exit();
}

// 💾 HANDLE FORM SUBMISSION: UPDATE CURRENT ITEM
if (isset($_POST['update_item'])) {
    $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
    $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
    $quantity  = intval($_POST['quantity']);

    // Execute the SQL UPDATE query string rule pattern
    $update_sql = "UPDATE inventory SET item_name='$item_name', category='$category', quantity=$quantity WHERE id=$id";
    
    if (mysqli_query($conn, $update_sql)) {
        // Refresh local array snapshot data for immediate interface display synchronization
        $item['item_name'] = $item_name;
        $item['category']  = $category;
        $item['quantity']  = $quantity;
        
        $success_msg = "የዕቃው መረጃ በተሳካ ሁኔታ ተሻሽሏል! (Item updated successfully!)";
        echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 1500);</script>";
    } else {
        $error_msg = "መረጃውን ማሻሻል አልተቻለም (Error): " . mysqli_error($conn);
    }
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-dark mb-1">የዕቃ መረጃ ማሻሻያ ቅጽ (Edit Item)</h2>
        <p class="text-muted mb-0">ለንብረት ኮድ <strong class="text-primary"><?php echo htmlspecialchars($item['item_code']); ?></strong> የተቀመጡ እሴቶችን እዚህ ይለውጡ።</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> ወደ መዝገብ ተመለስ
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card card-custom p-4 p-md-5">
            <form method="POST">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው መለያ ኮድ (ሊቀየር አይችልም)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0 text-muted" value="<?php echo htmlspecialchars($item['item_code']); ?>" readonly style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው ምድብ (Category) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-tags"></i></span>
                            <select name="category" class="form-select border-start-0 ps-0" required style="border-radius: 0 8px 8px 0;">
                                <option value="ኤሌክትሮኒክስ" <?php if($item['category'] == 'ኤሌክትሮኒክስ') echo 'selected'; ?>>ኤሌክትሮኒክስ (Electronics)</option>
                                <option value="አልባሳት" <?php if($item['category'] == 'አልባሳት') echo 'selected'; ?>>አልባሳት (Garments)</option>
                                <option value="የቢሮ ዕቃዎች" <?php if($item['category'] == 'የቢሮ ዕቃዎች') echo 'selected'; ?>>የቢሮ ዕቃዎች (Office Supplies)</option>
                                <option value="የድምፅ እቃዎች" <?php if($item['category'] == 'የድምፅ እቃዎች') echo 'selected'; ?>>የድምፅ እቃዎች (Audio Gear)</option>
                                <option value="ሌሎች" <?php if($item['category'] == 'ሌሎች') echo 'selected'; ?>>ሌሎች (Others)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው ሙሉ ስም (Item Name) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-box"></i></span>
                            <input type="text" name="item_name" class="form-control border-start-0 ps-0" value="<?php echo htmlspecialchars($item['item_name']); ?>" required style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">የክምችት ብዛት (Stock Quantity) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-layer-group"></i></span>
                            <input type="number" name="quantity" class="form-control border-start-0 ps-0" min="0" value="<?php echo intval($item['quantity']); ?>" required style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-12 pt-3 border-top mt-4 d-flex gap-2 justify-content-end">
                        <a href="dashboard.php" class="btn btn-light rounded-pill px-4">ተመለስ (Cancel)</a>
                        <button type="submit" name="update_item" class="btn btn-primary rounded-pill px-5 fw-semibold">
                            <i class="fas fa-check-circle me-2"></i> ለውጦችን አስቀምጥ
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

