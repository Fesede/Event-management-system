<?php 
include 'include/conn.php';
include 'include/header.php';

// የፍለጋ እና የማጣሪያ እሴቶችን በደህንነት መቀበል
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';

// የሁሉም ክፍላት ዝርዝር ድርድር (Array)
$categories = [
    'አባላት ጉዳይ ክፍል', 'ሕጻናት ንኡስ ክፍል', 'ማእከላውያን ንኡስ ክፍል', 'ወጣት ንኡስ ክፍል', 
    'ግቢ ጉባኤያት ንኡስ ክፍል', 'ትምህርት ዐቢይ ክፍል', 'ቤተ መጻሕፍት ንኡስ ክፍል', 'ምዝገባና መረጃ ክፍል', 
    'መዝሙር ንኡስ ክፍል', 'ኪነ ጥበብ ንኡስ ክፍል', 'የክሂሎት ማበልጸጊያ ንኡስ ክፍል', 'ጸሎትና መስተንግዶ ንኡስ ክፍል', 
    'የበጎ አድራጎት ንኡስ ክፍል', 'የገጠር ቤ/ክ ልማት ንኡስ ክፍል', 'ልማት ንኡስ ክፍል', 'ጉዞ ንኡስ ክፍል', 
    'ድጋፍና ማስተባበር ንኡስ ክፍል', 'ንዋይ አስተዳደር ዐቢይ ክፍል', 'የሰው ሀብት አስተዳደር ዐቢይ ክፍል', 
    'ጎልማሳ ክፍል', 'ምክር ክፍል', 'None'
];

// ዋናውን የSQL ኩዌሪ መገንባት (ሁኔታው 'published' የሆኑትን ብቻ)
$sql = "SELECT * FROM events WHERE status = 'published'";

if (!empty($search)) {
    $sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%')";
}
if (!empty($category) && $category !== 'All' && $category !== 'ሁሉም ክፍላት') {
    $sql .= " AND category = '$category'";
}
if (!empty($date)) {
    $sql .= " AND DATE(event_date) = '$date'";
}

$sql .= " ORDER BY event_date ASC";
$events_query = mysqli_query($conn, $sql);
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | St.Raguel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .event-hero {
            background: linear-gradient(rgba(13, 110, 253, 0.85), rgba(15, 23, 42, 0.9)), url('assets/image/head1.png');
            background-size: cover; background-position: center;
            color: white; padding: 80px 0; margin-bottom: 0;
        }
        .filter-bar {
            background: white; padding: 25px; border-radius: 16px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
            margin-top: -40px; position: relative; z-index: 10; border: none !important;
        }
        .event-card {
            border: none; border-radius: 16px;
            box-shadow: 0 4px 18px -4px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px -5px rgba(0,0,0,0.1);
        }
        .event-img-container { position: relative; height: 200px; overflow: hidden; }
        .event-img-container img { width: 100%; height: 100%; object-fit: cover; }
        .date-badge {
            position: absolute; top: 15px; left: 15px;
            background: white; color: #0d6efd; padding: 8px 14px;
            border-radius: 12px; font-weight: 800; text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); line-height: 1.2;
        }
    </style>
</head>
<body>

    <section class="event-hero text-center">
        <div class="container">
            <h1 class="display-5 fw-bold" id="header-title">ኩነቶችን ይፈልጉ</h1>
            <p class="lead text-white-50" id="header-sub">የየክፍላቱን ወቅታዊ መርሐ ግብሮች፣ ስብሰባዎች እና መግለጫዎች እዚህ ያግኙ።</p>
            <div class="mt-2">
                <button id="btn-am" class="btn btn-sm rounded-pill px-3 me-2 btn-light" onclick="changeLanguage('am')">አማርኛ</button>
                <button id="btn-en" class="btn btn-sm rounded-pill px-3 btn-outline-light" onclick="changeLanguage('en')">English</button>
            </div>
        </div>
    </section>

    <div class="container mb-5">
        <div class="filter-bar">
            <form action="events.php" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control py-2" id="search-placeholder" placeholder="የኩነት ስም ይፈልጉ..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-3">
                    <select name="category" class="form-select py-2" id="cat-select">
                        <option value="" id="cat-all">ሁሉም ክፍላት</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control py-2" value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" id="btn-search">ፈልግ</button>
                </div>
            </form>
        </div>
    </div>

    <section class="py-4">
        <div class="container">
            <div class="row g-4">
                <?php if (mysqli_num_rows($events_query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($events_query)): 
                        $event_timestamp = strtotime($row['event_date']);
                        $day = date('d', $event_timestamp);
                        $month_en = date('M', $event_timestamp);
                        
                        $months_am = ['Jan'=>'ጥሪ', 'Feb'=>'የካ', 'Mar'=>'መጋ', 'Apr'=>'ሚያ', 'May'=>'ግን', 'Jun'=>'ሰኔ', 'Jul'=>'ሐም', 'Aug'=>'ነሐ', 'Sep'=>'መስ', 'Oct'=>'ጥቅ', 'Nov'=>'ህዳ', 'Dec'=>'ታህ'];
                        $month_am = $months_am[$month_en] ?? $month_en;
                    ?>
                    <div class="col-md-4">
                        <div class="card event-card shadow-sm h-100">
                            <div class="event-img-container">
                                <img src="<?php echo !empty($row['image']) ? 'uploads/'.$row['image'] : 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&w=500'; ?>" alt="Event">
                                <div class="date-badge">
                                    <span class="d-block fs-4"><?php echo $day; ?></span>
                                    <small class="text-uppercase data-month-element" data-am="<?php echo $month_am; ?>" data-en="<?php echo $month_en; ?>"><?php echo $month_am; ?></small>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill small fw-bold mb-2"><?php echo htmlspecialchars($row['category']); ?></span>
                                <h5 class="card-title fw-bold text-dark mt-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($row['Location']); ?></p>
                            </div>
                            <div class="card-footer bg-white border-0 pb-4 pt-0">
                                <a href="view_event.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100 btn-details fw-bold rounded-pill">ዝርዝር ይመልከቱ</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="far fa-calendar-times text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted fw-bold">ምንም ኩነት አልተገኘም</h5>
                        <p class="text-muted small mb-0">ያቀረቡትን የፍለጋ መመዘኛ ቀይረው እንደገና ይሞክሩ።</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'include/footer.php'; ?>

    <script>
        const translations = {
            am: {
                headerTitle: "ኩነቶችን ይፈልጉ",
                headerSub: "በአዲስ አበባ የሚገኙ ወርክሾፖችን፣ ሴሚናሮችን እና ስብሰባዎችን ያግኙ።",
                searchPlaceholder: "የኩነት ስም ይፈልጉ...",
                catAll: "ሁሉም ክፍላት",
                btnSearch: "ፈልግ", btnDetails: "ዝርዝር ይመልከቱ"
            },
            en: {
                headerTitle: "Discover Events",
                headerSub: "Find meetings, schedules, and announcements from all departments.",
                searchPlaceholder: "Search event names...",
                catAll: "All Departments",
                btnSearch: "Search", btnDetails: "View Details"
            }
        };

        function changeLanguage(lang) {
            document.getElementById('header-title').textContent = translations[lang].headerTitle;
            document.getElementById('header-sub').textContent = translations[lang].headerSub;
            document.getElementById('search-placeholder').placeholder = translations[lang].searchPlaceholder;
            document.getElementById('cat-all').textContent = translations[lang].catAll;
            document.getElementById('btn-search').textContent = translations[lang].btnSearch;
            
            document.querySelectorAll('.btn-details').forEach(btn => {
                btn.textContent = translations[lang].btnDetails;
            });

            document.querySelectorAll('.data-month-element').forEach(el => {
                el.textContent = el.getAttribute('data-' + lang);
            });

            document.getElementById('btn-am').classList.toggle('btn-light', lang === 'am');
            document.getElementById('btn-am').classList.toggle('btn-outline-light', lang !== 'am');
            document.getElementById('btn-en').classList.toggle('btn-light', lang === 'en');
            document.getElementById('btn-en').classList.toggle('btn-outline-light', lang !== 'en');
            localStorage.setItem('preferredLang', lang);
        }

        window.onload = () => {
            const savedLang = localStorage.getItem('preferredLang') || 'am';
            changeLanguage(savedLang);
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>