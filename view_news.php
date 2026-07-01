<?php
include 'include/conn.php';
include 'include/header.php';

// የዜናው ID በ URL መምጣቱን እና ባዶ አለመሆኑን ማረጋገጥ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: News.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// የተመረጠውን ዜና መረጃ ማውጣት
$query = mysqli_query($conn, "SELECT * FROM news WHERE id = '$id' AND status = 'published'");
$article = mysqli_fetch_assoc($query);

// ዜናው በዳታቤዝ ውስጥ ካልተገኘ ወደ ዋናው የዜና ገጽ ይመልሰዋል
if (!$article) {
    header("Location: News.php");
    exit();
}

// የይዘት አምድ ስም 'message' ወይም 'description' መሆኑን ማረጋገጥ (Warning እንዳይሰጥ)
$article_content = $article['message'] ?? $article['description'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> | St.Raguel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .article-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            background: white;
            padding: 40px;
        }
        .article-img {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .sidebar-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
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
                <article class="article-card">
                    <div class="mb-3">
                        <span class="badge bg-opacity-10 text-primary bg-primary px-3 py-2 rounded-pill fw-bold">
                            <?php echo htmlspecialchars($article['category'] ?? 'General'); ?>
                        </span>
                    </div>

                    <h1 class="fw-bold text-dark mb-3 display-6"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <div class="d-flex align-items-center text-muted small mb-4 pb-3 border-bottom">
                        <span class="me-3"><i class="far fa-calendar-alt me-1"></i> <?php echo date('F d, Y', strtotime($article['created_at'])); ?></span>
                        <span><i class="far fa-clock me-1"></i> <?php echo date('h:i A', strtotime($article['created_at'])); ?></span>
                    </div>

                    <?php if (!empty($article['image'])): ?>
                        <img src="uploads/<?php echo $article['image']; ?>" class="article-img shadow-sm" alt="Article Image">
                    <?php endif; ?>

                    <div class="article-body text-secondary lh-lg" style="font-size: 1.1rem; text-align: justify; white-space: pre-line;">
                        <?php echo htmlspecialchars($article_content); ?>
                    </div>

                    <div class="mt-5 pt-4 border-top">
                        <a href="News.php" class="btn btn-light border text-muted px-4 py-2 rounded-pill small" id="btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Back to News
                        </a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <div class="card sidebar-card text-white border-0 p-4 mb-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <h5 class="fw-bold mb-2" id="side-news">የዜና መጽሔት</h5>
                    <p class="small text-white-50 mb-4" id="side-news-desc">የቅርብ ጊዜ መረጃዎች በኢሜልዎ እንዲደርስዎት ሰብስክራይብ ያድርጉ።</p>
                    <form>
                        <div class="mb-2">
                            <input type="email" class="form-control bg-white bg-opacity-10 border-0 text-white placeholder-white-50 py-2" placeholder="ኢሜል" required style="border-radius: 8px;">
                        </div>
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm" id="btn-sub" style="border-radius: 8px;">ይመዝገቡ</button>
                    </form>
                </div>

                <div class="card sidebar-card p-4 bg-white text-center">
                    <i class="fas fa-church text-primary fa-2x mb-3"></i>
                    <h6 class="fw-bold text-dark">St. Raguel Ministry</h6>
                    <p class="text-muted small mb-0">ለበለጠ መረጃ እና ለክፍል አገልግሎቶች ዋናውን አስተዳደር ያነጋግሩ።</p>
                </div>
            </div>
        </div>
    </div>

<?php 
include 'include/footer.php';
?>

    <script>
        const translations = {
            am: {
                btnBack: "ወደ ዜና ይመለሱ",
                sideNews: "የዜና መጽሔት", 
                sideNewsDesc: "የቅርብ ጊዜ መረጃዎች በኢሜልዎ እንዲደርስዎት ሰብስክራይብ ያድርጉ።",
                btnSub: "ይመዝገቡ"
            },
            en: {
                btnBack: "Back to News",
                sideNews: "Newsletter", 
                sideNewsDesc: "Get the latest updates delivered straight to your inbox.",
                btnSub: "Subscribe"
            }
        };

        function changeLanguage(lang) {
            document.getElementById('btn-back').innerHTML = `<i class="fas fa-arrow-left me-2"></i>${translations[lang].btnBack}`;
            document.getElementById('side-news').textContent = translations[lang].sideNews;
            document.getElementById('side-news-desc').textContent = translations[lang].sideNewsDesc;
            document.getElementById('btn-sub').textContent = translations[lang].btnSub;

            // Active ቁልፎችን ማስተካከል።
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>