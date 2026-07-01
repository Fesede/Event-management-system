<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'include/conn.php';

// Grab news records from database query
$news_result = mysqli_query($conn, "SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
$events_result = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");

include 'include/header.php';
?>

<style>
  .hero-section {
    background: linear-gradient(rgba(30, 60, 114, 0.8), rgba(42, 82, 152, 0.8)),
                url("assets/image/head1.png") no-repeat center center/cover;
    color: white;
    padding: 120px 0;
  }
  .news-card {
    border: none;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
  }
  .news-card:hover {
    transform: translateY(-3px);
  }
  .event-card img {
    height: 210px;
    object-fit: cover;
  }
</style>

<header id="home" class="hero-section text-center">
    <div class="container">
        <h1 class="display-3 fw-bold" id="hero-title">Church Youth Leadership Vision</h1>
        <p class="lead" id="hero-sub">Building holistic skills for church and national growth.</p>
        <div class="mt-4">
            <a href="about.php" class="btn btn-warning btn-lg me-2 fw-bold" id="btn-browse">More Seen</a>
            <a href="events.php" class="btn btn-outline-light btn-lg fw-bold" id="btn-post-hero">All Events</a>
        </div>
    </div>
</header>

<section id="about" class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold mb-4" id="about-title">Spiritual Growth Through Service</h2>
                <p id="about-text">Empowering our community through holistic education and faith.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <span id="check1">Holistic Spiritual Education</span></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <span id="check2">Modern Media Outreach</span></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <span id="check3">Community Development Projects</span></li>
                </ul>
            </div>
            <div class="col-md-6">
                <img src="assets/image/head3.png" class="img-thumbnail rounded shadow" alt="Meeting">
            </div>
        </div>
    </div>
</section>

<section id="news" class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="fw-bold mb-5" id="news-title">Latest News</h2>
        <div class="row g-4 text-start">
            <?php 
            $i = 1;
            if ($news_result && mysqli_num_rows($news_result) > 0):
                while($news = mysqli_fetch_assoc($news_result)):
            ?>
                <div class="col-md-4">
                    <div class="p-4 news-card h-100">
                        <small class="text-primary fw-bold text-uppercase"><?php echo date('M d, Y', strtotime($news['created_at'])); ?></small>
                        <h5 class="mt-2 fw-bold text-dark" id="news<?php echo $i; ?>-head"><?php echo htmlspecialchars($news['title']); ?></h5>
                        <p class="text-muted mb-0 small" id="news<?php echo $i; ?>-text"><?php echo htmlspecialchars($news['content']); ?></p>
                    </div>
                </div>
            <?php 
                $i++;
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center text-muted py-4">No recent news posts found.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="events" class="py-5 bg-white">
    <div class="container">
        <h3 class="fw-bold mb-4" id="upcoming-title">Upcoming Events</h3>
        <div class="row g-4">
            <?php 
            if ($events_result && mysqli_num_rows($events_result) > 0):
                while($row = mysqli_fetch_assoc($events_result)):
                    $img_filename = !empty($row['image']) ? htmlspecialchars($row['image']) : '';
                    $img_src = !empty($img_filename) ? "uploads/" . $img_filename : "https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&w=500";
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 event-card border-0 shadow-sm">
                        <img src="<?php echo $img_src; ?>" class="card-img-top" alt="Event Graphic Link">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($row['Location']); ?> | 
                                <i class="fas fa-calendar me-2"></i><?php echo date('Y-m-d', strtotime($row['event_date'])); ?>
                            </p>
                            <p class="card-text text-secondary small"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center text-muted py-4">There are no upcoming events listed right now.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'include/footer.php'; ?>

<script>
const translations = {
  am: {
    navHome: "መነሻ", navAbout: "ስለ እኛ", navNews: "ዜና", navEvents: "መርሐ ግብራት", navContact: "ግንኙነት",
    hTitle: "የቤተ ክርስቲያን ወጣቶች የአመራር ርእይ", hSub: "ለቤተ ክርስቲያንና ለሀገር እድገት ሁለንተናዊ ክህሎትን በንቃት መገንባት።",
    bBrowse: "ተጨማሪ ይመልከቱ", bPost: "ሁሉንም ኢቨንት",
    aTitle: "በአገልግሎት የሚገኝ ጥልቅ መንፈሳዊ እድገት", aText: "ማህበረሰባችንን በሁለንተናዊ ትምህርት እና በሃይማኖት ጥንካሬ ማብቃት።",
    c1: "ሁለንተናዊ መንፈሳዊ ትምህርት", c2: "ዘመናዊ የመገናኛ ብዙኃን አገልግሎት", c3: "የማህበረሰብ ልማት ፕሮጀክቶች",
    nTitle: "የቅርብ ጊዜ ዜናዎች", uTitle: "የሚመጡ ኩነቶች"
  },
  en: {
    navHome: "Home", navAbout: "About Us", navNews: "News", navEvents: "Events", navContact: "Contact",
    hTitle: "Church Youth Leadership Vision", hSub: "Building holistic skills for church and national growth.",
    bBrowse: "More Seen", bPost: "All Events",
    aTitle: "Spiritual Growth Through Service", aText: "Empowering our community through holistic education and faith.",
    c1: "Holistic Spiritual Education", c2: "Modern Media Outreach", c3: "Community Development Projects",
    nTitle: "Latest News", uTitle: "Upcoming Events"
  }
};

function changeLanguage(lang) {
  const updateTxt = (id, text) => { if(document.getElementById(id)) document.getElementById(id).textContent = text; };
  
  updateTxt("nav-home", translations[lang].navHome);
  updateTxt("nav-about", translations[lang].navAbout);
  updateTxt("nav-news", translations[lang].navNews);
  updateTxt("nav-events", translations[lang].navEvents);
  updateTxt("nav-contact", translations[lang].navContact);
  updateTxt("hero-title", translations[lang].hTitle);
  updateTxt("hero-sub", translations[lang].hSub);
  updateTxt("btn-browse", translations[lang].bBrowse);
  updateTxt("btn-post-hero", translations[lang].bPost);
  updateTxt("about-title", translations[lang].aTitle);
  updateTxt("about-text", translations[lang].aText);
  updateTxt("check1", translations[lang].c1);
  updateTxt("check2", translations[lang].c2);
  updateTxt("check3", translations[lang].c3);
  updateTxt("news-title", translations[lang].nTitle);
  updateTxt("upcoming-title", translations[lang].uTitle);

  localStorage.setItem('preferredLang', lang);
}

window.addEventListener('DOMContentLoaded', () => {
  const savedLang = localStorage.getItem("preferredLang") || "en";
  changeLanguage(savedLang);
});
</script>