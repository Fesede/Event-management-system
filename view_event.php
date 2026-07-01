<?php
include 'include/conn.php';
include 'include/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM events WHERE id = '$id' AND status = 'published'");
$event = mysqli_fetch_assoc($query);

if (!$event) {
    header("Location: events.php");
    exit();
}

$desc_content = $event['description'] ?? $event['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> | Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .details-card {
            border: none; border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            background: white; padding: 40px;
        }
        .details-img {
            width: 100%; max-height: 400px; object-fit: cover;
            border-radius: 16px; margin-bottom: 30px;
        }
        .info-box {
            background-color: #f1f5f9; border-radius: 12px; padding: 20px;
        }
    </style>
</head>
<body>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col text-end">
                <button id="btn-am" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2" onclick="changeLanguage('am')">አማርኛ</button>
                <button id="btn-en" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="changeLanguage('en')">English</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="details-card">
                    <div class="mb-3">
                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold">
                            <?php echo htmlspecialchars($event['category']); ?>
                        </span>
                    </div>

                    <h1 class="fw-bold text-dark mb-4"><?php echo htmlspecialchars($event['title']); ?></h1>

                    <?php if (!empty($event['image'])): ?>
                        <img src="uploads/<?php echo $event['image']; ?>" class="details-img shadow-sm" alt="Event">
                    <?php endif; ?>

                    <h5 class="fw-bold text-dark mb-3" id="lbl-about">ስለ ኩነቱ ማብራሪያ</h5>
                    <div class="text-secondary lh-lg mb-4" style="font-size: 1.05rem; white-space: pre-line; text-align: justify;">
                        <?php echo htmlspecialchars($desc_content); ?>
                    </div>

                    <div class="mt-5 pt-4 border-top">
                        <a href="events.php" class="btn btn-light border text-muted px-4 py-2 rounded-pill small" id="btn-back">
                            <i class="fas fa-arrow-left me-2"></i>ወደ ኩነቶች ተመለስ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card details-card p-4 info-box border-0">
                    <h5 class="fw-bold text-dark mb-4" id="lbl-details"><i class="far fa-clock text-primary me-2"></i>የኩነቱ ዝርዝር</h5>
                    
                    <div class="mb-3 d-flex align-items-start">
                        <i class="fas fa-calendar-day text-primary mt-1 me-3 fs-5"></i>
                        <div>
                            <strong class="d-block text-dark small" id="lbl-date">ቀን</strong>
                            <span class="text-secondary small"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                        </div>
                    </div>

                    <div class="mb-3 d-flex align-items-start">
                        <i class="far fa-clock text-primary mt-1 me-3 fs-5"></i>
                        <div>
                            <strong class="d-block text-dark small" id="lbl-time">ሰዓት</strong>
                            <span class="text-secondary small"><?php echo date('h:i A', strtotime($event['event_date'])); ?></span>
                        </div>
                    </div>

                    <div class="mb-0 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt text-danger mt-1 me-3 fs-5"></i>
                        <div>
                            <strong class="d-block text-dark small" id="lbl-location">ቦታ</strong>
                            <span class="text-secondary small"><?php echo htmlspecialchars($event['Location']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>

    <script>
        const translations = {
            am: {
                lblAbout: "ስለ ኩነቱ ማብራሪያ", lblDetails: "የ... ኩነት ዝርዝር",
                lblDate: "ቀን", lblTime: "ሰዓት", lblLocation: "ቦታ", btnBack: "ወደ ኩነቶች ተመለስ"
            },
            en: {
                lblAbout: "Event Description", lblDetails: "Event Schedule",
                lblDate: "Date", lblTime: "Time", lblLocation: "Venue", btnBack: "Back to Events"
            }
        };

        function changeLanguage(lang) {
            document.getElementById('lbl-about').textContent = translations[lang].lblAbout;
            document.getElementById('lbl-details').innerHTML = `<i class="far fa-clock text-primary me-2"></i>${translations[lang].lblDetails}`;
            document.getElementById('lbl-date').textContent = translations[lang].lblDate;
            document.getElementById('lbl-time').textContent = translations[lang].lblTime;
            document.getElementById('lbl-location').textContent = translations[lang].lblLocation;
            document.getElementById('btn-back').innerHTML = `<i class="fas fa-arrow-left me-2"></i>${translations[lang].btnBack}`;

            document.getElementById('btn-am').classList.toggle('btn-primary', lang === 'am');
            document.getElementById('btn-am').classList.toggle('btn-outline-secondary', lang !== 'am');
            document.getElementById('btn-en').classList.toggle('btn-primary', lang === 'en');
            document.getElementById('btn-en').classList.toggle('btn-outline-secondary', lang !== 'en');
            localStorage.setItem('preferredLang', lang);
        }

        window.onload = () => {
            const savedLang = localStorage.getItem('preferredLang') || 'am';
            changeLanguage(savedLang);
        };
    </script>
</body>
</html>