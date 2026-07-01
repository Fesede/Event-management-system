<?php
include 'include/conn.php';
include 'include/header.php';

// 1. በጣም የቅርብ ጊዜ የሆነውን 1 ዜና ለFeatured Post ማውጣት (ሁኔታው published የሆነ)
$featured_query = mysqli_query($conn, "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 1");
$featured_post = mysqli_fetch_assoc($featured_query);

// Featured የሆነውን ዜና ID መያዝ (ከታች ዝርዝር ውስጥ እንዳይደገም)
$featured_id = $featured_post ? $featured_post['id'] : 0;

// 2. የቀሩትን የታተሙ ዜናዎች በሙሉ ማውጣት
$news_query = mysqli_query($conn, "SELECT * FROM news WHERE status = 'published' AND id != '$featured_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Announcements | St.Raguel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .featured-post {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border-radius: 20px;
            padding: 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        .news-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
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
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold text-dark" id="news-title">Latest Updates</h1>
                <p class="text-muted mb-0" id="news-sub">Stay informed with the newest trends and announcements.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button id="btn-am" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2" onclick="changeLanguage('am')">አማርኛ</button>
                <button id="btn-en" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="changeLanguage('en')">English</button>
            </div>
        </div>

        <?php if ($featured_post): ?>
        <div class="featured-post shadow-lg">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill" id="badge-trending">Trending</span>
                    <h2 class="display-5 fw-bold mb-3" id="feat-title"><?php echo htmlspecialchars($featured_post['title'] ?? ''); ?></h2>
                    <p class="lead text-white-50 mb-4" id="feat-desc">
                        <?php 
                        // የ 'message' ወይም 'description' አምድን በደህንነት መፈለግ (Warning እንዳይሰጥ)
                        $feat_content = $featured_post['message'] ?? $featured_post['description'] ?? '';
                        echo htmlspecialchars(mb_strimwidth($feat_content, 0, 150, "...")); 
                        ?>
                    </p>
                    <a href="about.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm" id="btn-read-more" style="background-color: #0d6efd; border: none;">Read Full Story</a>
                </div>
                <?php if (!empty($featured_post['image'])): ?>
                <div class="col-lg-5 mt-4 mt-lg-0 text-center">
                    <img src="uploads/<?php echo $featured_post['image']; ?>" class="img-fluid rounded-4 shadow" style="max-height: 300px; object-fit: cover; width: 100%;" alt="Featured">
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="row g-4">
                    <?php if (mysqli_num_rows($news_query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($news_query)): ?>
                            <div class="col-md-6">
                                <div class="card news-card h-100">
                                    <img src="<?php echo !empty($row['image']) ? 'uploads/'.$row['image'] : 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=500'; ?>" class="card-img-top" alt="News Image">
                                    <div class="card-body d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="badge bg-opacity-10 text-primary bg-primary px-3 py-1 rounded-pill small fw-bold">
                                                <?php echo htmlspecialchars($row['category'] ?? 'General'); ?>
                                            </span>
                                        </div>
                                        <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title'] ?? ''); ?></h5>
                                        <p class="card-text text-muted small flex-grow-1">
                                            <?php 
                                            // የ 'message' ወይም 'description' አምድን በደህንነት መፈለግ (Warning እንዳይሰጥ)
                                            $card_content = $row['message'] ?? $row['description'] ?? '';
                                            echo htmlspecialchars(mb_strimwidth($card_content, 0, 100, "...")); 
                                            ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : ''; ?></small>
                                            <a href="view_news.php?id=<?php echo $row['id']; ?>" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-bold">Read More <i class="fas fa-arrow-right ms-1" style="font-size: 11px;"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 bg-white rounded-4 border shadow-sm">
                            <i class="fas fa-newspaper text-muted fa-3x mb-3"></i>
                            <h5 class="text-muted fw-bold">No More News Articles</h5>
                            <p class="text-muted small mb-0">Check back later for fresh updates and announcements.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4 mt-5 mt-lg-0">
                <div class="card sidebar-card p-4 mb-4 bg-white">
                    <h5 class="fw-bold mb-3 text-dark" id="side-search">ዜና ይፈልጉ</h5>
                    <form action="news.php" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0" id="search-input" placeholder="ቁልፍ ቃላትን ያስገቡ...">
                            <button class="btn btn-primary px-3" type="submit" style="background-color: #0d6efd; border: none;"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>

                <div class="card sidebar-card text-white border-0 p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <h5 class="fw-bold mb-2" id="side-news">የዜና መጽሔት</h5>
                    <p class="small text-white-50 mb-4" id="side-news-desc">የቅርብ ጊዜ መረጃዎች በኢሜልዎ እንዲደርስዎት ሰብስክራይብ ያድርጉ።</p>
                    <form>
                        <div class="mb-2">
                            <input type="email" class="form-control bg-white bg-opacity-10 border-0 text-white placeholder-white-50 py-2" placeholder="ኢሜል" required style="border-radius: 8px;">
                        </div>
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm" id="btn-sub" style="border-radius: 8px;">ይመዝገቡ</button>
                    </form>
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
                newsTitle: "የቅርብ ጊዜ መረጃዎች", 
                newsSub: "አዳዲስ ሁነቶችን እና ወቅታዊ መረጃዎችን እዚህ ያግኙ።",
                badgeTrending: "ወቅታዊ", 
                btnReadMore: "ሙሉውን ያንብቡ", 
                sideSearch: "ዜና ይፈልጉ", 
                searchInput: "ቁልፍ ቃላትን ያስገቡ...",
                sideNews: "የዜና መጽሔት", 
                sideNewsDesc: "የቅርብ ጊዜ መረጃዎች በኢሜልዎ እንዲደርስዎት ሰብስክራይብ ያድርጉ።",
                btnSub: "ይመዝገቡ"
            },
            en: {
                newsTitle: "Latest Updates", 
                newsSub: "Stay informed with the newest trends and announcements.",
                badgeTrending: "Trending", 
                btnReadMore: "Read Full Story", 
                sideSearch: "Search News", 
                searchInput: "Search keywords...",
                sideNews: "Newsletter", 
                sideNewsDesc: "Get the latest updates delivered straight to your inbox.",
                btnSub: "Subscribe"
            }
        };

        function changeLanguage(lang) {
            document.getElementById('news-title').textContent = translations[lang].newsTitle;
            document.getElementById('news-sub').textContent = translations[lang].newsSub;
            
            if(document.getElementById('badge-trending')) {
                document.getElementById('badge-trending').textContent = translations[lang].badgeTrending;
            }
            if(document.getElementById('btn-read-more')) {
                document.getElementById('btn-read-more').textContent = translations[lang].btnReadMore;
            }

            document.getElementById('side-search').textContent = translations[lang].sideSearch;
            document.getElementById('search-input').placeholder = translations[lang].searchInput;
            document.getElementById('side-news').textContent = translations[lang].sideNews;
            document.getElementById('side-news-desc').textContent = translations[lang].sideNewsDesc;
            document.getElementById('btn-sub').textContent = translations[lang].btnSub;

            // Active/Inactive state toggling
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