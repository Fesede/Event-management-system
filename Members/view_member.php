<?php 
$page_title = "የአባል መረጃ ዝርዝር መመልከቻ | View Profile";
$active_tab = "members"; 

require_once 'includes/attendance.php';
attendance_require_roles(['super admin', 'members manager', 'members']);
include 'includes/connect.php'; 

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: members.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    attendance_require_roles(['super admin', 'members manager']);
    attendance_validate_csrf();

    if (($_POST['action'] ?? '') !== 'rotate_qr') {
        http_response_code(400);
        exit('Unknown member action.');
    }

    try {
        $conn->begin_transaction();
        $lookup = $conn->prepare('SELECT qr_token FROM members WHERE id = ? FOR UPDATE');
        if (!$lookup) {
            throw new RuntimeException('Could not prepare the member QR lookup: ' . $conn->error);
        }
        $lookup->bind_param('i', $id);
        if (!$lookup->execute()) {
            throw new RuntimeException('Could not load the current QR code: ' . $lookup->error);
        }
        $current = $lookup->get_result()->fetch_assoc();
        $lookup->close();
        if (!$current) {
            throw new RuntimeException('Member not found.');
        }

        $new_token = bin2hex(random_bytes(16));
        $update = $conn->prepare('UPDATE members SET qr_token = ? WHERE id = ?');
        if (!$update) {
            throw new RuntimeException('Could not prepare the QR code update: ' . $conn->error);
        }
        $update->bind_param('si', $new_token, $id);
        if (!$update->execute()) {
            throw new RuntimeException('Could not update the QR code: ' . $update->error);
        }
        $update->close();
        attendance_log(
            $conn,
            null,
            $id,
            'member_qr_rotated',
            ['qr_available' => !empty($current['qr_token'])],
            ['qr_available' => true],
            'QR code reissued by member manager'
        );
        $conn->commit();
        header('Location: view_member.php?id=' . $id . '&status=qr_reissued');
        exit();
    } catch (RuntimeException $exception) {
        $conn->rollback();
        $qr_error = $exception->getMessage();
    }
}

$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    header("Location: members.php");
    exit();
}

$qr_success = ($_GET['status'] ?? '') === 'qr_reissued';
// Format registration timestamp safely 
$registration_date = date('B፣ d ቀን Y ዓ.ም', strtotime($member['created_at']));
$photoPath = !empty($member['Photo']) ? '../' . $member['Photo'] : 'assets/image/default-avatar.png';
include 'includes/header.php';
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
        <?php if ($can_manage_members): ?><a href="edit_member.php?id=<?php echo $member['id']; ?>" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-edit me-2"></i> መረጃ አሻሽል
        </a><?php endif; ?>
    </div>
</div>

<?php if ($can_manage_members): ?>
    <?php if ($qr_success): ?><div class="alert alert-success">A new member QR code was issued. Previously printed codes will no longer work.</div><?php endif; ?>
    <?php if (!empty($qr_error)): ?><div class="alert alert-danger"><?php echo attendance_escape($qr_error); ?></div><?php endif; ?>
    <div class="card card-custom border-0 shadow-sm p-4 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <h5 class="fw-bold"><i class="fas fa-qrcode text-primary me-2"></i>Member attendance QR</h5>
                <p class="text-muted mb-2">This code contains an opaque member token, not personal details. Only an assigned Attendance Holder can use it to check in an active member.</p>
                <span class="badge text-bg-<?php echo ($member['membership_status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                    Membership <?php echo attendance_escape($member['membership_status'] ?? 'active'); ?>
                </span>
                <form method="post" class="mt-3" onsubmit="return confirm('Issue a new QR code? The old printed code will stop working.');">
                    <input type="hidden" name="csrf_token" value="<?php echo attendance_escape(attendance_csrf_token()); ?>">
                    <input type="hidden" name="action" value="rotate_qr">
                    <button class="btn btn-outline-danger btn-sm" type="submit"><i class="fas fa-rotate me-1"></i>Reissue QR code</button>
                </form>
            </div>
            <div class="col-md-4 text-center">
                <?php if (($member['membership_status'] ?? 'active') === 'active'): ?>
                    <div id="member-id-card" class="member-id-card text-start">
                        <div class="id-card-header">
                            <span><i class="fas fa-church me-2"></i>ST. RAGUEL</span>
                            <small>MEMBER CARD</small>
                        </div>
                        <div class="id-card-content">
                            <img class="id-card-photo" src="<?php echo htmlspecialchars($photoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                            <div class="id-card-details">
                                <span class="id-card-label">MEMBER NAME</span>
                                <strong><?php echo htmlspecialchars($member['full_Name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span class="id-card-number">ID #<?php echo (int)$member['id']; ?></span>
                            </div>
                            <div id="member-qr" class="id-card-qr" aria-label="Member attendance QR code"></div>
                        </div>
                        <div class="id-card-footer">CHURCH MEMBERSHIP · ATTENDANCE QR</div>
                    </div>
                    <div class="no-print"><button class="btn btn-primary btn-sm mt-3" type="button" onclick="window.print()"><i class="fas fa-id-card me-1"></i>Print ID Card</button></div>
                <?php else: ?>
                    <p class="text-muted mb-0">Activate this member before printing a usable attendance QR.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

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
<?php if ($can_manage_members && ($member['membership_status'] ?? 'active') === 'active'): ?>
<style>
.member-id-card {
    width: 360px;
    height: 227px;
    max-width: 100%;
    margin: 0 auto;
    padding: 14px;
    overflow: hidden;
    color: #17345f;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
    font-family: Arial, sans-serif;
}
.id-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 9px;
    color: #fff;
    border-bottom: 3px solid #2563eb;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .5px;
}
.id-card-header span {
    padding: 5px 8px;
    color: #fff;
    background: #1d4ed8;
    border-radius: 5px;
}
.id-card-header small {
    color: #1d4ed8;
    font-size: 9px;
    letter-spacing: 1px;
}
.id-card-content {
    display: flex;
    height: 147px;
    align-items: center;
    gap: 10px;
}
.id-card-photo {
    width: 58px;
    height: 72px;
    flex: 0 0 58px;
    object-fit: cover;
    border: 2px solid #dbeafe;
    border-radius: 6px;
}
.id-card-details {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 5px;
}
.id-card-label {
    color: #64748b;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: .8px;
}
.id-card-details strong {
    overflow-wrap: anywhere;
    color: #0f172a;
    font-size: 13px;
    line-height: 1.25;
}
.id-card-number {
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 700;
}
.id-card-qr {
    width: 100px;
    height: 100px;
    flex: 0 0 100px;
    padding: 4px;
    background: #fff;
    border: 1px solid #e2e8f0;
}
.id-card-qr img, .id-card-qr canvas {
    display: block;
    width: 90px;
    height: 90px;
}
.id-card-footer {
    padding-top: 6px;
    color: #64748b;
    border-top: 1px solid #e2e8f0;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: .7px;
    text-align: center;
}
@media print {
    @page { size: 85.6mm 54mm; margin: 0; }
    body * { visibility: hidden !important; }
    #member-id-card, #member-id-card * { visibility: visible !important; }
    #member-id-card {
        position: fixed;
        top: 0;
        left: 0;
        width: 85.6mm;
        height: 54mm;
        max-width: none;
        margin: 0;
        padding: 3mm;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    #member-id-card .id-card-content { height: 35mm; }
    #member-id-card .id-card-qr {
        width: 26mm;
        height: 26mm;
        flex-basis: 26mm;
    }
    #member-id-card .id-card-qr img,
    #member-id-card .id-card-qr canvas {
        width: 24mm;
        height: 24mm;
    }
    #member-id-card .id-card-photo {
        width: 16mm;
        height: 20mm;
        flex-basis: 16mm;
    }
    .no-print { display: none !important; }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
if (typeof QRCode !== 'undefined') {
    new QRCode(document.getElementById('member-qr'), {
        text: <?php echo json_encode($member['qr_token']); ?>,
        width: 192,
        height: 192,
        correctLevel: QRCode.CorrectLevel.M
    });
} else {
    document.getElementById('member-qr').textContent = 'QR library unavailable. Reload to try again.';
}
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
</body>
</html>
