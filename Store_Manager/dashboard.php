<?php 
$page_title = "Inventory Control Management";
$active_tab = "inventory"; 

// 1. Include modular layout header blocks and the master connection file
include 'include/header.php'; 
include 'include/conn.php'; 

$success_msg = "";
$error_msg = "";

// 🗑️ HANDLE DELETE ACTION OPERATION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Using a safe query to delete the selected item row
    $delete_sql = "DELETE FROM inventory WHERE id = $delete_id";
    if (mysqli_query($conn, $delete_sql)) {
        $success_msg = "ዕቃው ከመዝገብ ላይ በተሳካ ሁኔታ ተሰርዟል! (Item deleted successfully!)";
    } else {
        $error_msg = "ለማጥፋት ሲሞከር ስህተት አጋጥሟል፡ " . mysqli_error($conn);
    }
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-sm-6">
        <h2 class="fw-bold text-dark mb-1">የዕቃዎች ክምችት ቁጥጥር (Inventory)</h2>
        <p class="text-muted mb-0">በመጋዘን ውስጥ የሚገኙ ንብረቶችን ሙሉ ዝርዝር መዝገብ እዚህ ያስተዳድሩ።</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="add_inventory.php" class="btn btn-primary rounded-pill px-4 py-2 small fw-semibold">
            <i class="fas fa-plus me-2"></i> አዲስ ዕቃ መዝግብ
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

<div class="card card-custom overflow-hidden">
    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>የንብረት ዝርዝር መዝገብ</h5>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small">
            <i class="fas fa-cubes me-1 text-primary"></i> Live DB Storage
        </span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small uppercase">
                <tr>
                    <th class="ps-4 py-3">የዕቃው ኮድ (Code)</th>
                    <th class="py-3">የዕቃው ስም (Item Name)</th>
                    <th class="py-3">ምድብ (Category)</th>
                    <th class="py-3">ብዛት (Qty)</th>
                    <th class="py-3">ሁኔታ (Status)</th>
                    <th class="text-end pe-4 py-3">ድርጊት (Actions)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch inventory rows sorted so latest additions appear at the top
                $inventory_query = mysqli_query($conn, "SELECT * FROM inventory ORDER BY id DESC");
                
                if (mysqli_num_rows($inventory_query) > 0) {
                    while ($row = mysqli_fetch_assoc($inventory_query)) {
                        $qty = intval($row['quantity']);
                        
                        // Setup conditional styling threshold markers based on database numeric values
                        if ($qty >= 10) {
                            $status_badge = "<span class='badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-medium'>በቂ ክምችት</span>";
                        } elseif ($qty > 0 && $qty < 10) {
                            $status_badge = "<span class='badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1 fw-medium'>እያለቀ ያለ</span>";
                        } else {
                            $status_badge = "<span class='badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 fw-medium'>ያለቀ</span>";
                        }

                        echo "<tr>
                            <td class='ps-4 fw-mono text-secondary small'>".htmlspecialchars($row['item_code'])."</td>
                            <td class='fw-bold text-dark'>".htmlspecialchars($row['item_name'])."</td>
                            <td><span class='text-muted small'><i class='fas fa-tag me-1 text-opacity-25 text-dark'></i>".htmlspecialchars($row['category'])."</span></td>
                            <td class='fw-bold fs-6 ".($qty < 5 ? "text-danger" : "text-dark")."'>".$qty."</td>
                            <td>".$status_badge."</td>
                            <td class='text-end pe-4'>
                                <div class='btn-group' role='group'>
                                    <a href='edit_item.php?id=".$row['id']."' class='btn btn-light btn-sm text-primary border-0 rounded-circle me-1' title='አስተካክል (Edit)'>
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <a href='delete_item.php?id=".$row['id']."' class='btn btn-light btn-sm text-danger border-0 rounded-circle' title='አጥፋ (Delete)' onclick='return confirm(\"እርግጠኛ ነዎት ይህ ዕቃ ከሲስተሙ ላይ በቋሚነት ይሰረዝ? (Are you sure you want to delete this item?)\");'>
                                        <i class='fas fa-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>";
                    }
                } else {
                    // Empty state fallback display when no database row entries exist
                    echo "<tr>
                        <td colspan='6' class='text-center py-5 text-muted'>
                            <div class='py-4'>
                                <i class='fas fa-box-open d-block text-secondary opacity-50 mb-3 display-5'></i>
                                <h5 class='fw-semibold text-dark mb-1'>ምንም የተመዘገበ ዕቃ የለም</h5>
                                <p class='text-muted small mb-3'>በአሁኑ ሰዓት በመጋዘኑ ውስጥ ምንም ንብረት አልተመዘገበም።</p>
                                <a href='add_inventory.php' class='btn btn-sm btn-primary rounded-pill px-3'>
                                    <i class='fas fa-plus me-1'></i> የመጀመሪያውን ዕቃ እዚህ ይመዝግቡ
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

