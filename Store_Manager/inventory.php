<?php 
$page_title = "Dashboard | Store Manager";
$active_tab = "dashboard"; 

// 1. Include structural layout header template and database connectivity credentials
include 'include/header.php'; 
include 'include/conn.php'; 

// 📊 2. FETCH LIVE INVENTORY METRICS & SUMMARY STATS
// Stat 1: Total Unique Item Types Registered
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory");
$total_data  = mysqli_fetch_assoc($total_query);
$total_items = $total_data['total'] ?? 0;

// Stat 2: Low Stock Warning Items Count (Quantities strictly less than 5)
$low_query = mysqli_query($conn, "SELECT COUNT(*) as low_count FROM inventory WHERE quantity < 5");
$low_data  = mysqli_fetch_assoc($low_query);
$low_stock = $low_data['low_count'] ?? 0;

// Stat 3: Total Cumulative Sum of all units in warehouse storage
$qty_query  = mysqli_query($conn, "SELECT SUM(quantity) as total_qty FROM inventory");
$qty_data   = mysqli_fetch_assoc($qty_query);
$total_quantity = $qty_data['total_qty'] ?? 0;
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold text-dark mb-1">የመጋዘን አስተዳዳሪ ዳሽቦርድ (Dashboard)</h2>
        <p class="text-muted mb-0">እቃዎችን፣ አጠቃላይ ክምችቶችንና ልዩ ክትትል የሚሹ ንብረቶችን እዚህ ይቆጣጠሩ።</p>
    </div>
</div>

<div class="row g-4 mb-5">
    
    <div class="col-md-4">
        <div class="card card-custom p-4 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                    <i class="fas fa-boxes fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold uppercase mb-1">የተመዘገቡ ዕቃዎች ዓይነቶች</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_items); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-custom p-4 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold uppercase mb-1">ያለቁ/ያነሱ ዕቃዎች (ከ 5 በታች)</h6>
                    <h3 class="fw-bold mb-0 <?php echo ($low_stock > 0) ? 'text-danger' : 'text-dark'; ?>">
                        <?php echo number_format($low_stock); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-custom p-4 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                    <i class="fas fa-dolly fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary small fw-semibold uppercase mb-1">አጠቃላይ የእቃ ክምችት ድምር</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_quantity); ?> <span class="fs-6 fw-normal text-muted">ፍሬ</span></h3>
                </div>
            </div>
        </div>
    </div>
    
</div>

<div class="card card-custom overflow-hidden border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-danger mb-0 d-flex align-items-center">
            <i class="fas fa-bell me-2 animate-pulse"></i> ልዩ ክትትል የሚሹ ዕቃዎች (Low Stock Alerts)
        </h5>
        <span class="badge <?php echo ($low_stock > 0) ? 'bg-danger' : 'bg-success'; ?> rounded-pill px-3 py-2">
            <?php echo $low_stock; ?> እቃዎች ማሟላት አለባቸው
        </span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4 py-3">የዕቃው ኮድ (Code)</th>
                    <th class="py-3">የዕቃው ስም (Item Name)</th>
                    <th class="py-3">ምድብ (Category)</th>
                    <th class="py-3">የቀረው ብዛት (Qty Left)</th>
                    <th class="py-3">የክምችት ሁኔታ (Status)</th>
                    <th class="text-end pe-4 py-3">ድርጊት (Action)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch only products falling strictly below the danger margin threshold of 5 units
                $low_stock_list = mysqli_query($conn, "SELECT * FROM inventory WHERE quantity < 5 ORDER BY quantity ASC");
                
                if (mysqli_num_rows($low_stock_list) > 0) {
                    while($item = mysqli_fetch_assoc($low_stock_list)) {
                        $qty_val = intval($item['quantity']);
                        
                        // Setup dynamic alert flags based on complete exhaustion vs minimum levels
                        if ($qty_val === 0) {
                            $badge_markup = "<span class='badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-medium'>በፍጹም አልቋል (Empty)</span>";
                        } else {
                            $badge_markup = "<span class='badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1 small fw-medium'>ሊያልቅ የቀረብ</span>";
                        }

                        echo "<tr>
                            <td class='ps-4 fw-mono text-secondary small'>".htmlspecialchars($item['item_code'])."</td>
                            <td class='fw-bold text-dark'>".htmlspecialchars($item['item_name'])."</td>
                            <td><span class='text-muted small'><i class='fas fa-tag me-1 text-muted opacity-50'></i>".htmlspecialchars($item['category'])."</span></td>
                            <td class='text-danger fw-bold fs-6'>".$qty_val."</td>
                            <td>".$badge_markup."</td>
                            <td class='text-end pe-4'>
                                <a href='edit_item.php?id=".$item['id']."' class='btn btn-primary btn-sm rounded-pill px-3 py-1' style='font-size: 0.75rem;'>
                                    <i class='fas fa-plus me-1'></i> ክምችት ጨምር
                                </a>
                            </td>
                        </tr>";
                    }
                } else {
                    // Safe optimal clear state representation container
                    echo "<tr>
                        <td colspan='6' class='text-center py-5 text-muted'>
                            <div class='py-3'>
                                <i class='fas fa-check-circle text-success mb-2 display-6 d-block opacity-70'></i>
                                <h6 class='fw-semibold text-dark mb-1'>በአሁኑ ሰዓት ክምችት የቀነሰበት እቃ የለም!</h6>
                                <p class='text-muted small mb-0'>ሁሉም የንብረት ዓይነቶች በመጋዘኑ ውስጥ ከደህንነት ወለል በላይ በበቂ ሁኔታ ይገኛሉ።</p>
                            </div>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

