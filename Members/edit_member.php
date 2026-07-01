<?php 
$page_title = "የአባል መረጃ ማሻሻያ ማዕከል | Edit Profile";
$active_tab = "members"; 

include 'includes/header.php'; 
include 'includes/connect.php'; 

$error_msg = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: members.php");
    exit();
}

$id = intval($_GET['id']);
$fetch_res = mysqli_query($conn, "SELECT * FROM members WHERE id = $id");
$member = mysqli_fetch_assoc($fetch_res);

if (!$member) {
    header("Location: members.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function clean_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $photo_db_path = $member['Photo']; 
    
    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] === 0) {
        $max_size = 5 * 1024 * 1024;
        if ($_FILES['Photo']['size'] > $max_size) {
            $error_msg = "የፎቶው መጠን ከ 5MB መብለጥ የለበትም።";
        } else {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['Photo']['name'], PATHINFO_EXTENSION));
            $newName = uniqid('raguel_', true) . '.' . $ext;
            $targetFile = $uploadDir . $newName;

            $allowed = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowed)) {
                if (move_uploaded_file($_FILES['Photo']['tmp_name'], $targetFile)) {
                    $photo_db_path = 'uploads/' . $newName;
                } else {
                    $error_msg = "ፎቶውን ወደ ሰርቨር መጫን አልተቻለም።";
                }
            } else {
                $error_msg = "እባክዎ ምስል (JPG ወይም PNG) ብቻ ይጠቀሙ።";
            }
        }
    }

    if (empty($error_msg)) {
        $f_name   = clean_input($_POST['full_Name'] ?? '');
        $p_no     = clean_input($_POST['Phone_no'] ?? '');
        $k_name   = clean_input($_POST['kesitena_Name'] ?? '');
        $twn      = clean_input($_POST['town'] ?? '');
        $k_ketema = clean_input($_POST['Kefele_Ketema'] ?? '');
        $wrd      = clean_input($_POST['Werda'] ?? '');
        $s_level  = clean_input($_POST['school_level'] ?? '');
        $s_work   = clean_input($_POST['status_work'] ?? '');
        $t_work   = clean_input($_POST['type_work'] ?? '');
        $s_select = clean_input($_POST['Service_Selection'] ?? '');
        $e_name   = clean_input($_POST['Emg_name'] ?? '');
        $e_phone  = clean_input($_POST['Emg_phone'] ?? '');
        $rel      = clean_input($_POST['Relationship'] ?? '');

        $update_sql = "UPDATE members SET Photo=?, full_Name=?, Phone_no=?, kesitena_Name=?, town=?, Kefele_Ketema=?, Werda=?, school_level=?, status_work=?, type_work=?, Service_Selection=?, Emg_name=?, Emg_phone=?, Relationship=? WHERE id=?";
        
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("ssssssssssssssi", 
                $photo_db_path, $f_name, $p_no, $k_name, 
                $twn, $k_ketema, $wrd, $s_level, 
                $s_work, $t_work, $s_select, 
                $e_name, $e_phone, $rel, $id
            );

            if ($stmt->execute()) {
                echo "<script>window.location.href = 'members.php?status=updated';</script>";
                exit();
            } else {
                $error_msg = "የመረጃ ማሻሻያ ስህተት፦ " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-dark mb-1">የአባል መረጃ ማስተካከያ ገጽ</h2>
        <p class="text-muted mb-0">ማሻሻል የሚፈልጉትን መረጃ እዚህ ቀይረው ያስቀምጡ።</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="members.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i> ሰርዝ</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form id="regForm" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="fas fa-user-edit text-primary me-2"></i>የአባሉ መለያ መረጃ ማስተካከያ</h5>
                <div class="row g-4 align-items-center">
                    <div class="col-md-3 text-center border-end">
                        <div class="bg-light border mx-auto d-flex align-items-center justify-content-center overflow-hidden rounded-circle mb-2" style="width:120px; height:120px;">
                            <img id="img-el" src="<?php echo !empty($member['Photo']) ? '../' . $member['Photo'] : 'assets/image/default-avatar.png'; ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                        <label for="p-input" class="btn btn-xs btn-dark rounded-pill px-2 py-1 small" style="font-size:0.75rem;">ፎቶ ቀይር</label>
                        <input type="file" id="p-input" name="Photo" hidden onchange="handlePhoto(this)">
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">ሙሉ ስም *</label>
                                <input type="text" name="full_Name" class="form-control" value="<?php echo htmlspecialchars($member['full_Name']); ?>" required style="border-radius:8px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">ስልክ ቁጥር *</label>
                                <input type="tel" name="Phone_no" id="phone" class="form-control" value="<?php echo htmlspecialchars($member['Phone_no']); ?>" required style="border-radius:8px;">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-secondary">የክርስትና ስም</label>
                                <input type="text" name="kesitena_Name" class="form-control" value="<?php echo htmlspecialchars($member['kesitena_Name']); ?>" style="border-radius:8px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="fas fa-map text-primary me-2"></i>አድራሻ እና ስራ</h5>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">ከተማ *</label><input type="text" name="town" class="form-control" value="<?php echo htmlspecialchars($member['town']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">ክፍለ ከተማ *</label><input type="text" name="Kefele_Ketema" class="form-control" value="<?php echo htmlspecialchars($member['Kefele_Ketema']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">ወረዳ *</label><input type="text" name="Werda" class="form-control" value="<?php echo htmlspecialchars($member['Werda']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">የትምህርት ደረጃ *</label><input type="text" name="school_level" class="form-control" value="<?php echo htmlspecialchars($member['school_level']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">የስራ ሁኔታ *</label><input type="text" name="status_work" class="form-control" value="<?php echo htmlspecialchars($member['status_work']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">የስራ አይነት *</label><input type="text" name="type_work" class="form-control" value="<?php echo htmlspecialchars($member['type_work']); ?>" required style="border-radius:8px;"></div>
                    
                    <div class="col-12 mt-4">
                        <label class="form-label small fw-semibold text-secondary">የተመረጡ አገልግሎት ክፍሎች (እስከ 3 ክፍሎች) *</label>
                        <div class="p-4 border border-dashed text-center rounded-3 bg-light cursor-pointer" onclick="toggleDepts()" style="cursor:pointer; border-style: dashed !important;">
                            <div id="sel-text" class="fw-medium text-primary"><?php echo !empty($member['Service_Selection']) ? htmlspecialchars($member['Service_Selection']) : 'የአገልግሎት ክፍሎችን ለመምረጥ እዚህ ይጫኑ'; ?></div>
                        </div>
                        <input type="hidden" name="Service_Selection" id="final-depts" value="<?php echo htmlspecialchars($member['Service_Selection']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm mb-5">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="fas fa-user-shield text-primary me-2"></i>የድንገተኛ ተጠሪ መረጃ</h5>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">የተጠሪ ስም *</label><input type="text" name="Emg_name" class="form-control" value="<?php echo htmlspecialchars($member['Emg_name']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">የተጠሪ ስልክ *</label><input type="tel" name="Emg_phone" class="form-control" value="<?php echo htmlspecialchars($member['Emg_phone']); ?>" required style="border-radius:8px;"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold text-secondary">ዝምድና *</label><input type="text" name="Relationship" class="form-control" value="<?php echo htmlspecialchars($member['Relationship']); ?>" required style="border-radius:8px;"></div>
                </div>

                <div class="col-12 pt-4 border-top mt-5 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold" id="submit-btn">ለውጦችን አሻሽል</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="dept-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.98); z-index:9999; padding:40px 20px; overflow-y:auto;">
     <div class="text-center mb-4">
         <h2 class="text-warning fw-bold"><i class="fas fa-edit me-2"></i>የአገልግሎት ክፍሎች ማሻሻያ ማዕከል</h2>
     </div>
     <div id="dept-list" class="d-flex flex-wrap gap-2 justify-content-center mx-auto" style="max-width:1100px;"></div>
     <div class="text-center mt-5">
         <button class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow-lg" onclick="toggleDepts()">ማሻሻያውን ጨርስ</button>
     </div>
</div>

<script>
    let picks = <?php echo !empty($member['Service_Selection']) ? json_encode(explode(', ', $member['Service_Selection'])) : '[]'; ?>;
    // Complete Array containing all 35 items synced with index.html structures 
    const depts = [
        'ትምህርት ክፍል', 'መዝሙር ክፍል', 'ልማትና ገቢ ክፍል', 'የአባላት ክትትል ክፍል', 'አርድእት ክፍል', 
        'ምክር ክፍል', 'ጥናትና ምርምር ክፍል', 'ሚዲያ ክፍል', 'ማህበራት ማስተባበር ክፍል', 'ስብከተ ወንጌል ክፍል', 
        'ነባር አባላት ክፍል', 'አባላት ጉዳይ ክፍል', 'ህፃናት ንዑስ ክፍል', 'ማዕከላዊያን ንዑስ ክፍል', 'ወጣት ንዑስ ክፍል', 
        'ጊቢ ጉባኤያት ክፍል', 'ሥርዓተ ትምህርት ቀረጻና ማሻሻያ ንዑስ ክፍል', 'መረጃና ምዝገባ ንዑስ ክፍል', 'ተከታታይ ትምህርት ንዑስ ክፍል', 
        'ቤተ መጸሐፍት ንዑስ ክፍል', 'አርድዕትና አብነት ትምህርት ማበልፀጊያ ንዑስ ክፍል', 'መዝሙር ንዑስ ክፍል', 'ኪነ ጥበብ ንዑስ ክፍል', 
        'ክህሎት ማበልጸጊያ ንዑስ ክፍል', 'በጎ አድራጎት ንዑስ ክፍል', 'የገጠር አቢያተ ቤተ ክርስቲያን ንዑስ ክፍል', 'ልማት ንዑስ ክፍል', 
        'ጉዞ ንዑስ ክፍል', 'ድጋፍና ማስተባበር ንዑስ ክፍል', 'ሒሳብና በጀት ንዑስ ክፍል', 'ግዢ ንዑስ ክፍል', 
        'ግምጃ ቤት ንዑስ ክፍል', 'ቤተ መዛግብት ንዑስ ክፍል', 'የሰው ሀብት አስተዳደር ንዑስ ክፍል', 'ጎልማሳ ክፍል'
    ];

    function handlePhoto(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = e => { document.getElementById('img-el').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleDepts() {
        const modal = document.getElementById('dept-modal');
        if(modal.style.display === 'none') {
            const listEl = document.getElementById('dept-list');
            listEl.innerHTML = depts.map(d => {
                const isSelected = picks.includes(d);
                return `
                    <div class="card p-3 text-center cursor-pointer m-1" 
                         style="min-width:260px; cursor:pointer; background:${isSelected ? '#1e293b' : '#0f172a'}; color:${isSelected ? '#ffcf40' : '#fff'}; border:${isSelected ? '2px solid #ffcf40 !important' : '1px solid #334155'}" 
                         onclick="pick('${d}', this)">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-medium">${d}</span>
                            <i class="fas ${isSelected ? 'fa-check-circle text-warning' : 'fa-circle text-secondary opacity-20'}"></i>
                        </div>
                    </div>
                `;
            }).join('');
            modal.style.display = 'block';
        } else {
            modal.style.display = 'none';
            document.getElementById('sel-text').innerHTML = picks.length > 0 ? picks.map(p => `<span class='badge bg-primary me-1'>${p}</span>`).join(' ') : 'የአገልግሎት ክፍሎችን ለመምረጥ እዚህ ይጫኑ';
            document.getElementById('final-depts').value = picks.join(', ');
        }
    }

    function pick(d, el) {
        const icon = el.querySelector('i');
        if(picks.includes(d)) {
            picks = picks.filter(x => x !== d);
            el.style.background = '#0f172a';
            el.style.color = '#fff';
            el.style.setProperty('border', '1px solid #334155', 'important');
            icon.className = 'fas fa-circle text-secondary opacity-20';
        } else {
            if(picks.length < 3) {
                picks.push(d);
                el.style.background = '#1e293b';
                el.style.color = '#ffcf40';
                el.style.setProperty('border', '2px solid #ffcf40', 'important');
                icon.className = 'fas fa-check-circle text-warning';
            } else { alert('ከ 3 በላይ የአገልግሎት ክፍል መምረጥ አይችሉም!'); }
        }
    }

    function validateForm() {
        const phone = document.getElementById('phone').value;
        if(phone.length < 10) { alert('እባክዎ ትክክለኛ ስልክ ቁጥር ያስገቡ!'); return false; }
        if(picks.length === 0) { alert('እባክዎ ቢያንስ አንድ የአገልግሎት ክፍል ይምረጡ!'); return false; }
        document.getElementById('submit-btn').innerHTML = "<i class='fas fa-spinner fa-spin me-2'></i>በማሻሻል ላይ...";
        return true;
    }
</script>
