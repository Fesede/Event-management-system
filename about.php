<style>
  .about-header {
    background: linear-gradient(rgba(30, 60, 114, 0.85), rgba(30, 60, 114, 0.85)),
                url("assets/image/head1.png") no-repeat center center/cover;
    color: white;
    padding: 90px 0;
    text-align: center;
  }
  .service-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
  }
</style>

<?php 
include 'include/header.php';
?>

<header class="about-header">
    <div class="container">
        <h1 class="display-4 fw-bold" id="header-title">Our History</h1>
        <p class="lead opacity-90" id="header-sub">Debre Hail St. Raguel Church Anqetse Berhan Sunday School</p>
    </div>
</header>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10 text-center">
                <h2 class="fw-bold text-dark mb-3" id="history-title">Sunday School Activities</h2>
                <p class="text-muted fs-5 lh-base" id="history-desc">
                    Established in June 1967 (1959 E.C.), Anqetse Berhan Sunday School at Debre Hail St. Raguel Church has played a vital role in nurturing generations. Today, it operates with a modernized structure, providing spiritual education to hundreds of students through regular, distance, and online programs. Beyond education, the Sunday school is actively engaged in media, research, and community services. Its primary future goal is the construction of a seven-story multi-purpose building to further expand its apostolic mission and educational facilities.
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 service-card h-100 text-center">
                    <div class="mb-3"><i class="fas fa-book-open fa-3x text-primary"></i></div>
                    <h4 class="fw-bold text-dark" id="card1-title">Education</h4>
                    <p class="text-muted small mb-0" id="card1-desc">Consecutive 4-year formal spiritual education programs.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 service-card h-100 text-center">
                    <div class="mb-3"><i class="fas fa-church fa-3x text-primary"></i></div>
                    <h4 class="fw-bold text-dark" id="card2-title">Service</h4>
                    <p class="text-muted small mb-0" id="card2-desc">Choir services at festivals and regular prayer programs.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 service-card h-100 text-center">
                    <div class="mb-3"><i class="fas fa-lightbulb fa-3x text-primary"></i></div>
                    <h4 class="fw-bold text-dark" id="card3-title">Vision</h4>
                    <p class="text-muted small mb-0" id="card3-desc">A plan to build a 7-story multipurpose service building.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
include 'include/footer.php';
?>

<script>
const translations = {
  am: {
    navHome: "መነሻ", navAbout: "ስለ እኛ", navNews: "ዜና", navEvents: "መርሐ ግብራት", navContact: "ግንኙነት",
    headerTitle: "የሰንበት ትምህርት ቤቱ ታሪክ",
    headerSub: "የደብረ ኃይል ቅዱስ ራጉኤል ቤ/ክ አንቀጸ ብርሃን ሰንበት ትምህርት ቤት",
    historyTitle: "የሰንበት ትምህርት ቤቱ እንቅስቃሴ",
    historyDesc: "በደብረ ኃይል ቅዱስ ራጉኤል ቤተ ክርስቲያን የሚገኘው የአንቀጸ ብርሃን ሰንበት ትምህርት ቤት፣ በሰኔ ወር 1959 ዓ.ም ከተመሰረተበት ጊዜ ጀምሮ ለትውልድ ግንባታ ትልቅ አስተዋጽኦ እያበረከተ ይገኛል። በአሁኑ ወቅት ዘመኑን በዋጀ መልኩ ተደራጅቶ በመደበኛ፣ በርቀት እና በኦንላይን አማካኝነት በመቶዎች የሚቆጠሩ ተማሪዎችን በመንፈሳዊ ትምህርት እያነጸ ይገኛል። ከትምህርት በተጨማሪ በሚዲያ፣ በጥናትና ምርምር እንዲሁም በማህበራዊ አገልግሎቶች ንቁ ተሳትፎ የሚያደርግ ሲሆን፣ የወደፊት ትልቅ ግቡም ሰባት ወለል ያለው ሁለገብ የትምህርትና የአገልግሎት ሕንጻ በመገንባት ሐዋርያዊ ተልዕኮውን ይበልጥ ማስፋፋት ነው።",
    card1Title: "ትምህርት", card1Desc: "ተከታታይ የአራት ዓመት መደበኛ መንፈሳዊ ትምህርቶች።",
    card2Title: "አገልግሎት", card2Desc: "በንግሥ በዓላት መዝሙር ማቅረብ እና የጸሎት መርሐግብር።",
    card3Title: "ራዕይ", card3Desc: "ባለ ሰባት ወለል ሁለገብ ሕንጻ የመገንባት እቅድ።"
  },
  en: {
    navHome: "Home", navAbout: "About Us", navNews: "News", navEvents: "Events", navContact: "Contact",
    headerTitle: "Our History",
    headerSub: "Debre Hail St. Raguel Church Anqetse Berhan Sunday School",
    historyTitle: "Sunday School Activities",
    historyDesc: "Established in June 1967 (1959 E.C.), Anqetse Berhan Sunday School at Debre Hail St. Raguel Church has played a vital role in nurturing generations. Today, it operates with a modernized structure, providing spiritual education to hundreds of students through regular, distance, and online programs. Beyond education, the Sunday school is actively engaged in media, research, and community services. Its primary future goal is the construction of a seven-story multi-purpose building to further expand its apostolic mission and educational facilities.",
    card1Title: "Education", card1Desc: "Consecutive 4-year formal spiritual education programs.",
    card2Title: "Service", card2Desc: "Choir services at festivals and regular prayer programs.",
    card3Title: "Vision", card3Desc: "A plan to build a 7-story multipurpose service building."
  }
};

function changeLanguage(lang) {
  // Graceful DOM fallback injector function
  const updateTxt = (id, text) => {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  };

  // Main Nav Hooks
  updateTxt("nav-home", translations[lang].navHome);
  updateTxt("nav-about", translations[lang].navAbout);
  updateTxt("nav-news", translations[lang].navNews);
  updateTxt("nav-events", translations[lang].navEvents);
  updateTxt("nav-contact", translations[lang].navContact);
  
  // Header and Paragraph Context
  updateTxt("header-title", translations[lang].headerTitle);
  updateTxt("header-sub", translations[lang].headerSub);
  updateTxt("history-title", translations[lang].historyTitle);
  updateTxt("history-desc", translations[lang].historyDesc);
  
  // Icon Cards Content
  updateTxt("card1-title", translations[lang].card1Title);
  updateTxt("card1-desc", translations[lang].card1Desc);
  updateTxt("card2-title", translations[lang].card2Title);
  updateTxt("card2-desc", translations[lang].card2Desc);
  updateTxt("card3-title", translations[lang].card3Title);
  updateTxt("card3-desc", translations[lang].card3Desc);

  // Language Button States Synchronization
  const btnAm = document.getElementById("btn-am");
  const btnEn = document.getElementById("btn-en");
  if (btnAm) btnAm.classList.toggle("active", lang === "am");
  if (btnEn) btnEn.classList.toggle("active", lang === "en");
  
  localStorage.setItem("preferredLang", lang);
}

// Ensure execution triggers safely after document rendering complete
window.addEventListener('DOMContentLoaded', () => {
  const savedLang = localStorage.getItem("preferredLang") || "am";
  changeLanguage(savedLang);
});
</script>