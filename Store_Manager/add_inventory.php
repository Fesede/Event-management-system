<?php 
$page_title = "Add New Inventory Item";
$active_tab = "inventory"; // Keeps the Inventory menu item highlighted

// 1. Include layout elements and global database connection
include 'include/header.php'; 
include 'include/conn.php'; 

$success_msg = "";
$error_msg = "";

// 📥 HANDLE FORM SUBMISSION: INSERT NEW ITEM
if (isset($_POST['submit_item'])) {
    $item_code = mysqli_real_escape_string($conn, trim($_POST['item_code']));
    $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
    $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
    $quantity  = intval($_POST['quantity']);

    // Check for duplicate item code to prevent database crashes
    $check_dup = mysqli_query($conn, "SELECT id FROM inventory WHERE item_code = '$item_code'");
    if (mysqli_num_rows($check_dup) > 0) {
        $error_msg = "ይህ የዕቃ ኮድ ቀደም ብሎ ተመዝግቧል። እባክዎ ሌላ ኮድ ይጠቀሙ። (This item code already exists.)";
    } else {
        $insert_sql = "INSERT INTO inventory (item_code, item_name, category, quantity) 
                       VALUES ('$item_code', '$item_name', '$category', '$quantity')";
        
        if (mysqli_query($conn, $insert_sql)) {
            $success_msg = "አዲስ ዕቃ በተሳካ ሁኔታ ተመዝግቧል! (Item added successfully!)";
            // Optional: Redirect back to inventory index sheet after a 2-second delay
            echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 2000);</script>";
        } else {
            $error_msg = "ስህተት አጋጥሟል (Error): " . mysqli_error($conn);
        }
    }
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-dark mb-1">አዲስ ዕቃ መመዝገቢያ ገጽ (Add New Item)</h2>
        <p class="text-muted mb-0">አዲስ የንብረት ዝርዝር መረጃዎችን በጥንቃቄ እዚህ ያስገቡ።</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> ወደ መዝገብ ተመለስ
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-times-circle me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-4 p-md-5">
            <form method="POST" action="add_inventory.php">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው መለያ ኮድ (Item Unique Code) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-barcode"></i></span>
                            <input type="text" name="item_code" class="form-control border-start-0 ps-0" placeholder="ምሳሌ: INV-0012" value="<?php echo isset($_POST['item_code']) ? htmlspecialchars($_POST['item_code']) : ''; ?>" required style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው ምድብ (Category) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-tags"></i></span>
                            <select name="category" class="form-select border-start-0 ps-0" required style="border-radius: 0 8px 8px 0;">
                                <option value="">-- ምድብ ይምረጡ --</option>
                                <option value="ኤሌክትሮኒክስ" <?php echo (isset($_POST['category']) && $_POST['category'] == 'ኤሌክትሮኒክስ') ? 'selected' : ''; ?>>ኤሌክትሮኒክስ (Electronics)</option>
                                <option value="አልባሳት" <?php echo (isset($_POST['category']) && $_POST['category'] == 'አልባሳት') ? 'selected' : ''; ?>>አልባሳት (Garments)</option>
                                <option value="የቢሮ ዕቃዎች" <?php echo (isset($_POST['category']) && $_POST['category'] == 'የቢሮ ዕቃዎች') ? 'selected' : ''; ?>>የቢሮ ዕቃዎች (Office Supplies)</option>
                                <option value="የድምፅ እቃዎች" <?php echo (isset($_POST['category']) && $_POST['category'] == 'የድምፅ እቃዎች') ? 'selected' : ''; ?>>የድምፅ እቃዎች (Audio Gear)</option>
                                <option value="ሌሎች" <?php echo (isset($_POST['category']) && $_POST['category'] == 'ሌሎች') ? 'selected' : ''; ?>>ሌሎች (Others)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary">የዕቃው ሙሉ ስም (Item Descriptive Name) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-box"></i></span>
                            <input type="text" name="item_name" class="form-control border-start-0 ps-0" placeholder="ምሳሌ: Sound Mixer Console ወይም Wireless Microphone" value="<?php echo isset($_POST['item_name']) ? htmlspecialchars($_POST['item_name']) : ''; ?>" required style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">መነሻ ብዛት (Initial Quantity Stock) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-layer-group"></i></span>
                            <input type="number" name="quantity" class="form-control border-start-0 ps-0" min="0" value="<?php echo isset($_POST['quantity']) ? intval($_POST['quantity']) : '0'; ?>" required style="border-radius: 0 8px 8px 0;">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">ያለዎትን የመጀመሪያ ክምችት መጠን እዚህ ያስገቡ።</div>
                    </div>

                    <div class="col-12 pt-3 border-top mt-4 d-flex gap-2 justify-content-end">
                        <button type="reset" class="btn btn-light rounded-pill px-4">ቅጹን አጽዳ (Clear)</button>
                        <button type="submit" name="submit_item" class="btn btn-primary rounded-pill px-5 fw-semibold">
                            <i class="fas fa-save me-2"></i> መረጃውን መዝግብ
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card card-custom p-4 border border-primary border-opacity-10 bg-primary bg-opacity-10 text-dark">
            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-info-circle me-2"></i> መመሪያዎች (Tips)</h5>
            <ul class="small ps-3 mb-0 text-secondary" style="line-height: 1.6;">
                <li class="mb-2"><strong class="text-dark">የዕቃ ኮድ፡</strong> እያንዳንዱ ንብረት የራሱ የሆነ የማይደገም የተለየ መለያ ኮድ ሊኖረው ይገባል።</li>
                <li class="mb-2"><strong class="text-dark">ምድብ፡</strong> ንብረቶችን በትክክለኛው ምድብ ማስቀመጥ በኋላ ላይ ሪፖርት ለማውጣትና ለመፈለግ ይረዳል።</li>
                <li><strong class="text-dark">ብዛት፡</strong> አዲስ የሚመዘገብ ንብረት ክምችት ከሌለው መጠኑን <code class="text-dark">0</code> አድርገው መተው ይችላሉ።</li>
            </ul>
        </div>
    </div>
</div>

