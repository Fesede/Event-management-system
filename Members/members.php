<?php 
$page_title = "የአባላት ስም ዝርዝር መዝገብ | Members Directory";
$active_tab = "members"; 

// Include local modular layout structure templates and global database connections
include 'includes/header.php'; 
include 'includes/connect.php'; // Maps your data connection strings cleanly

$success_msg = "";
$error_msg = "";

// 🔔 Capture processing feedback parameters routed back from CRUD controllers
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') {
        $success_msg = "አዲስ አባል በተሳካ ሁኔታ ተመዝግቧል! (Member registered successfully!)";
    } elseif ($_GET['status'] === 'updated') {
        $success_msg = "የአባሉ መረጃ በተሳካ ሁኔታ ተሻሽሏል! (Member records updated!)";
    } elseif ($_GET['status'] === 'deleted_success') {
        $success_msg = "አባሉ ከመዝገብ ላይ በቋሚነት ተሰርዟል! (Member dropped successfully!)";
    } elseif ($_GET['status'] === 'deleted_failed') {
        $error_msg = "አባሉን ከመዝገብ ላይ ለማጥፋት ሲሞከር ስህተት አጋጥሟል!";
    }
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-sm-6">
        <h2 class="fw-bold text-dark mb-1">የአባላት ሙሉ ዝርዝር መዝገብ</h2>
        <p class="text-muted mb-0">በሲስተሙ ውስጥ የተመዘገቡ አባላትን መረጃ እዚህ ያስተዳድሩ፣ ይፈልጉ ወይም ያሻሽሉ።</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="add_member.php" class="btn btn-primary rounded-pill px-4 py-2 small fw-semibold">
            <i class="fas fa-user-plus me-2"></i> አዲስ አባል መዝግብ
        </a>
    </div>
</div>

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

<div class="card card-custom overflow-hidden border-0 shadow-sm">
    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-address-book me-2 text-primary"></i>የአባላት ማውጫ መዝገብ</h5>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small">
            <i class="fas fa-database me-1 text-primary"></i> Live DB Core
        </span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4 py-3">ፎቶ (Photo)</th>
                    <th class="py-3">መለያ (ID)</th>
                    <th class="py-3">ሙሉ ስም / የክርስትና ስም</th>
                    <th class="py-3">ስልክ ቁጥር (Phone)</th>
                    <th class="py-3">አድራሻ (ከተማ/ክፍለ ከተማ/ወረዳ)</th>
                    <th class="py-3">የስራ ሁኔታና አይነት</th>
                    <th class="text-end pe-4 py-3">ድርጊት (Actions)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query pulling profiles ordered logically by latest insertion sequence
                $members_query = mysqli_query($conn, "SELECT * FROM members ORDER BY id DESC");
                
                if (mysqli_num_rows($members_query) > 0) {
                    while ($row = mysqli_fetch_assoc($members_query)) {
                        
                        // Manage responsive image paths targeting uploads fallback location
                        $photoPath = !empty($row['Photo']) ? '../' . $row['Photo'] : 'assets/image/default-avatar.png';
                        $avatar_img = "<img src='".htmlspecialchars($photoPath)."' class='rounded-circle shadow-sm border' style='width:45px; height:45px; object-fit:cover;'>";
                        
                        // Display work conditions together as status representations
                        $work_status = htmlspecialchars($row['status_work']);
                        $work_type = htmlspecialchars($row['type_work']);
                        
                        echo "<tr>
                            <td class='ps-4'>".$avatar_img."</td>
                            <td class='fw-mono text-secondary small'>#".htmlspecialchars($row['id'])."</td>
                            <td>
                                <div class='fw-bold text-dark mb-0'>".htmlspecialchars($row['full_Name'])."</div>";
                                if(!empty($row['kesitena_Name'])) {
                                    echo "<small class='text-primary fw-medium'><i class='fas fa-cross me-1 small'></i>የክርስትና ስም፦ ".htmlspecialchars($row['kesitena_Name'])."</small>";
                                }
                        echo "</td>
                            <td><span class='text-dark small font-monospace'><i class='fas fa-phone-alt me-1 text-muted opacity-50'></i>".htmlspecialchars($row['Phone_no'])."</span></td>
                            <td class='small text-muted'>
                                <div>".htmlspecialchars($row['town'])."፣ ".htmlspecialchars($row['Kefele_Ketema'])."</div>
                                <small class='text-secondary'>ወረዳ፦ ".htmlspecialchars($row['Werda'])."</small>
                            </td>
                            <td>
                                <span class='badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-medium'>
                                    <i class='fas fa-briefcase me-1 small'></i>".$work_status." (".$work_type.")
                                </span>
                            </td>
                            <td class='text-end pe-4'>
                                <div class='btn-group' role='group'>
                                    <a href='view_member.php?id=".$row['id']."' class='btn btn-light btn-sm text-info border-0 rounded-circle me-1' title='ሙሉ ማህደር ተመልከት'>
                                        <i class='fas fa-eye'></i>
                                    </a>
                                    <a href='edit_member.php?id=".$row['id']."' class='btn btn-light btn-sm text-primary border-0 rounded-circle me-1' title='መረጃ ቀይር'>
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <a href='delete_member.php?id=".$row['id']."' class='btn btn-light btn-sm text-danger border-0 rounded-circle' title='ከመዝገብ አጥፋ' onclick='return confirm(\"እርግጠኛ ነዎት ይህንን አባል ከሲስተሙ ላይ በቋሚነት መሰረዝ ይፈልጋሉ?\");'>
                                        <i class='fas fa-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr>
                        <td colspan='7' class='text-center py-5 text-muted'>
                            <div class='py-4'>
                                <i class='fas fa-folder-open d-block text-secondary opacity-50 mb-3 display-5'></i>
                                <h5 class='fw-semibold text-dark mb-1'>ምንም የተመዘገበ አባል የለም</h5>
                                <p class='text-muted small mb-3'>በአሁኑ ሰዓት በሲስተሙ ውስጥ ምንም የአባል መረጃ ዝርዝር አልተገኘም።</p>
                                <a href='add_member.php' class='btn btn-sm btn-primary rounded-pill px-3'>
                                    <i class='fas fa-user-plus me-1'></i> የመጀመሪያውን አባል እዚህ ይመዝግቡ
                                </a>
                            </div>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

