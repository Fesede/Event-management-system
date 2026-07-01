<?php 
$page_title = "የአባል መረጃ ዝርዝር መመልከቻ | View Profile";
$active_tab = "members"; 

include 'includes/header.php'; 
include 'includes/connect.php'; 

// 🔎 Validate if a valid ID target is passed into the routing path
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href = 'members.php';</script>";
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

// Fallback redirect if member record doesn't exist
if (!$member) {
    echo "<script>window.location.href = 'members.php';</script>";
    exit();
}

// Format registration timestamp safely 
$registration_date = date('B፣ d ቀን Y ዓ.ም', strtotime($member['created_at']));
$photoPath = !empty($member['Photo']) ? '../' . $member['Photo'] : 'assets/image/default-avatar.png';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-dark mb-1">የአባል ማህደር ዝርዝር መገለጫ</h2>
        <p class="text-muted mb-0">የአባሉን ሙሉ የምዝገባ፣ የማህበራዊ እና የአገልግሎት ክፍል መረጃዎችን ከዚህ በታች መመልከት ይችላሉ።</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="members.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">
            <i class="fas fa-arrow-left me-2"></i> ወደ ዝርዝር ተመለስ
        </a>
        <a href="edit_member.php?id=<?php echo $member['id']; ?>" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-edit me-2"></i> መረጃ አሻሽል
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-custom border-0 shadow-sm text-center p-4">
            <div class="position-relative mx-auto mb-4" style="width: 160px; height: 160px;">
                <img src="<?php echo htmlspecialchars($photoPath); ?>" 
                     class="rounded-circle border shadow-sm w-100 h-100 object-fit-cover" 
                     alt="Member Photo">
                <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center border border-white" 
                      style="width: 38px; height: 38px; font-size: 0.85rem;">
                    #<?php echo $member['id']; ?>
                </span>
            </div>
            
            <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($member['full_Name']); ?></h4>
            <?php if(!empty($member['kesitena_Name'])): ?>
                <p class="text-primary fw-medium mb-3"><i class="fas fa-cross me-1 small"></i> የክርስትና ስም፦ <?php echo htmlspecialchars($member['kesitena_Name']); ?></p>
            <?php endif; ?>

            <hr class="text-muted my-3 opacity-20">

            <div class="text-start mb-2">
                <small class="text-secondary d-block fw-semibold uppercase mb-1">የስራ ሁኔታና ዘርፍ</small>
                <div class="p-2 rounded bg-light border-start border-success border-3">
                    <span class="text-dark fw-bold small"><i class="fas fa-briefcase me-2 text-success"></i><?php echo htmlspecialchars($member['status_work']); ?></span>
                    <span class="text-muted small d-block ms-4">(<?php echo htmlspecialchars($member['type_work']); ?>)</span>
                </div>
            </div>

            <div class="text-start mt-3">
                <small class="text-secondary d-block fw-semibold uppercase mb-1">የተመዘገበበት ቀን</small>
                <span class="text-dark small"><i class="far fa-calendar-alt me-2 text-muted"></i><?php echo $registration_date; ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3">
                <i class="fas fa-id-card text-primary me-2"></i> ክፍል ፩፦ የግል እና የማህበራዊ ህይወት መረጃ
            </h5>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="text-secondary small d-block fw-medium">ሙሉ ስም (Full Name)</label>
                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($member['full_Name']); ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-secondary small d-block fw-medium">ስልክ ቁጥር (Phone Number)</label>
                    <span class="text-dark font-monospace fw-bold"><i class="fas fa-phone-alt me-2 text-muted opacity-50"></i><?php echo htmlspecialchars($member['Phone_no']); ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-secondary small d-block fw-medium">የክርስትና ስም (Christian Name)</label>
                    <span class="text-dark"><?php echo !empty($member['kesitena_Name']) ? htmlspecialchars($member['kesitena_Name']) : '—'; ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-secondary small d-block fw-medium">የትምህርት ደረጃ (Educational Level)</label>
                    <span class="text-dark"><i class="fas fa-graduation-cap me-2 text-muted opacity-50"></i><?php echo htmlspecialchars($member['school_level']); ?></span>
                </div>
            </div>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3">
                <i class="fas fa-map-marked-alt text-primary me-2"></i> ክፍል ፪፦ የመኖሪያ አድራሻ መዋቅር
            </h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">ከተማ (Town)</label>
                    <span class="text-dark fw-semibold"><?php echo htmlspecialchars($member['town']); ?></span>
                </div>
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">ክፍለ ከተማ (Sub City)</label>
                    <span class="text-dark fw-semibold"><?php echo htmlspecialchars($member['Kefele_Ketema']); ?></span>
                </div>
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">ወረዳ (Woreda)</label>
                    <span class="text-dark fw-semibold">ወረዳ <?php echo htmlspecialchars($member['Werda']); ?></span>
                </div>
            </div>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3">
                <i class="fas fa-church text-primary me-2"></i> ክፍል ፫፦ የተመረጡ የአገልግሎት ክፍላት
            </h5>
            <label class="text-secondary small d-block fw-medium mb-2">አባልነታቸውን ያረጋገጡባቸው ንዑስ ክፍላት</label>
            <div class="d-flex flex-wrap gap-2">
                <?php 
                if (!empty($member['Service_Selection'])) {
                    $selected_depts = explode(', ', $member['Service_Selection']);
                    foreach ($selected_depts as $dept) {
                        echo "<span class='badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold small'><i class='fas fa-bookmark me-2'></i>" . htmlspecialchars($dept) . "</span>";
                    }
                } else {
                    echo "<span class='text-muted small italic'>ምንም ክፍል አልተመረጠም።</span>";
                }
                ?>
            </div>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3">
                <i class="fas fa-user-shield text-primary me-2"></i> ክፍል ፬፦ የድንገተኛ ጊዜ ተጠሪ መረጃ (Emergency Contact)
            </h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">የተጠሪ ሙሉ ስም</label>
                    <span class="text-dark fw-bold"><i class="far fa-user me-2 text-muted opacity-50"></i><?php echo htmlspecialchars($member['Emg_name']); ?></span>
                </div>
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">የተጠሪ ስልክ ቁጥር</label>
                    <span class="text-dark font-monospace fw-semibold"><i class="fas fa-phone me-2 text-muted opacity-50"></i><?php echo htmlspecialchars($member['Emg_phone']); ?></span>
                </div>
                <div class="col-sm-4">
                    <label class="text-secondary small d-block fw-medium">ዝምድና</label>
                    <span class="badge bg-secondary text-white rounded-pill px-3 py-1.5 small fw-medium"><?php echo htmlspecialchars($member['Relationship']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

