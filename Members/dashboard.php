<?php 
$page_title = "Membership Dashboard";
$active_tab = "dashboard"; 

require_once 'includes/attendance.php';
if (in_array($attendance_role, ['attendance holder', 'attendance controller'], true)) {
    header('Location: attendance.php');
    exit();
}
include 'includes/header.php'; 
include 'includes/connect.php'; // Updated to map your global connection file path cleanly

// 📊 RUN DATA AGGREGATION QUERIES FOR STAT CARDS
// Stat 1: Total Cumulative Registered Members
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM members");
$total_data = mysqli_fetch_assoc($total_q);
$total_count = $total_data['total'] ?? 0;

// Stat 2: Active membership status
$active_q = mysqli_query($conn, "SELECT COUNT(*) as active_total FROM members WHERE membership_status='active'");
$active_data = mysqli_fetch_assoc($active_q);
$active_count = $active_data['active_total'] ?? 0;

// Stat 3: Inactive membership status
$inactive_q = mysqli_query($conn, "SELECT COUNT(*) as inactive_total FROM members WHERE membership_status='inactive'");
$inactive_data = mysqli_fetch_assoc($inactive_q);
$inactive_count = $inactive_data['inactive_total'] ?? 0;

// Stat 4: Recent enrollments within the last 30 days
$recent_q = mysqli_query($conn, "SELECT COUNT(*) as recent_total FROM members WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$recent_data = mysqli_fetch_assoc($recent_q);
$recent_count = $recent_data['recent_total'] ?? 0;
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold text-dark mb-1">የአባላት አስተዳደር ማጠቃለያ (Dashboard)</h2>
        <p class="text-muted mb-0">አጠቃላይ የስራ አፈጻጸም፣ የአባላት ሁኔታ እና አዳዲስ ምዝገባዎችን እዚህ ይከታተሉ::</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                    <i class="fas fa-users fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold text-uppercase mb-1">ጠቅላላ የተመዘገቡ አባላት</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_count); ?> <span class="fs-6 text-muted fw-normal">ሰው</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                    <i class="fas fa-user-check fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold text-uppercase mb-1">ንቁ አባላት (Active members)</h6>
                    <h3 class="fw-bold mb-0 text-success"><?php echo number_format($active_count); ?> <span class="fs-6 text-muted fw-normal">ሰው</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-3 me-3">
                    <i class="fas fa-user-slash fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold text-uppercase mb-1">Inactive members</h6>
                    <h3 class="fw-bold mb-0 text-secondary"><?php echo number_format($inactive_count); ?> <span class="fs-6 text-muted fw-normal">ሰው</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                    <i class="fas fa-user-plus fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold text-uppercase mb-1">በዚህ ወር የተመዘገቡ</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($recent_count); ?> <span class="fs-6 text-muted fw-normal">ሰው</span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm overflow-hidden mb-4">
    <div class="p-4 bg-white border-bottom">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-clock text-primary me-2"></i>የመጨረሻዎቹ አምስት አዳዲስ ተመዝጋቢዎች</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">መለያ ቁጥር (ID)</th>
                    <th>ሙሉ ስም (Full Name)</th>
                    <th>ስልክ ቁጥር (Phone)</th>
                    <th>የተቀላቀሉበት ቀን</th>
                    <th class="text-end pe-4">ድርጊት</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $latest_members = mysqli_query($conn, "SELECT * FROM members ORDER BY id DESC LIMIT 5");
                if (mysqli_num_rows($latest_members) > 0) {
                    while($m = mysqli_fetch_assoc($latest_members)) {
                        // Formatting the SQL timestamp to clean date format
                        $formatted_date = date('Y-m-d', strtotime($m['created_at']));
                        
                        echo "<tr>
                            <td class='ps-4 fw-mono text-secondary small'>#".htmlspecialchars($m['id'])."</td>
                            <td class='fw-bold text-dark'>".htmlspecialchars($m['full_Name'])."</td>
                            <td><span class='font-monospace'>".htmlspecialchars($m['Phone_no'])."</span></td>
                            <td class='small text-muted'>".$formatted_date."</td>
                            <td class='text-end pe-4'>
                                <a href='view_member.php?id=".$m['id']."' class='btn btn-sm btn-outline-secondary rounded-pill px-3 me-1'>
                                    <i class='fas fa-eye me-1'></i> መመልከት
                                </a>
                                ".($can_manage_members ? "
                                <a href='edit_member.php?id=".$m['id']."' class='btn btn-sm btn-outline-primary rounded-pill px-3'>
                                    <i class='fas fa-edit me-1'></i> መረጃ ቀይር
                                </a>" : "")."
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>እስካሁን ምንም የተመዘገበ አባል የለም::</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
