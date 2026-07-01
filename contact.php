
    <?php 
    include 'include/header.php'
    ?>

    <!-- CONTACT HEADER -->
    <section class="contact-header">
        <div class="container">
            <h1 class="fw-bold" id="header-title">Get In Touch</h1>
            <p class="lead" id="header-sub">Have questions or want to partner with us? We'd love to hear from you.</p>
        </div>
    </section>

    <!-- INFO CARDS -->
    <div class="container my-5">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card info-card p-4 text-center">
                    <div class="icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                    <h5 class="fw-bold" id="card1-title">Our Office</h5>
                    <p class="text-muted" id="card1-desc">Addis Ketema Sub City <br>Addis Ababa, Ethiopia</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card info-card p-4 text-center">
                    <div class="icon-circle"><i class="fas fa-phone-alt"></i></div>
                    <h5 class="fw-bold" id="card2-title">Phone Number</h5>
                    <p class="text-muted">+251975726868/0112764646<br><span id="hours">Mon - Fri, 8am - 6pm</span></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card info-card p-4 text-center">
                    <div class="icon-circle"><i class="fas fa-envelope"></i></div>
                    <h5 class="fw-bold" id="card3-title">Email Address</h5>
                    <p class="text-muted">dhsraguelabssedu21@gmail.com</p>
                </div>
            </div>
        </div>

        <!-- FORM & MAP -->
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold mb-4" id="form-title">መልዕክት ይላኩልን</h3>
                    <form id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" id="lbl-fname">ስም</label>
                                <input type="text" class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" id="lbl-lname">የአባት ስም</label>
                                <input type="text" class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold" id="lbl-email">ኢሜይል</label>
                                <input type="email" class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold" id="lbl-msg">መልዕክት</label>
                                <textarea class="form-control bg-light border-0" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="btn-submit">መልዕክት ላክ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="map-container shadow-sm h-100">
                    <iframe src="https://maps.app.goo.gl/HciNd6nvCfaqjgKu8" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER PART -->
   <?php 
   include 'include/footer.php';
   ?>
    <script>
        const translations = {
            am: {
                navHome: "መነሻ", navAbout: "ስለ እኛ", navNews: "ዜና", navContact: "ያግኙን",
                headerTitle: "ያግኙን",
                headerSub: "ጥያቄ አለዎት ወይስ ከእኛ ጋር መሥራት ይፈልጋሉ? ከእርስዎ መስማት እንወዳለን።",
                card1Title: "ቢሯችን", card1Desc: "አዲስ ከተማ ክፍለ ከተማ<br>አዲስ አበባ፣ ኢትዮጵያ",
                card2Title: "ስልክ ቁጥር", card2Hours: "ሰኞ - አርብ፣ 2 ሰዓት - 12 ሰዓት",
                card3Title: "ኢሜይል",
                formTitle: "መልዕክት ይላኩልን",
                lblFname: "ስም", lblLname: "የአባት ስም", lblEmail: "ኢሜይል", lblMsg: "መልዕክት",
                btnSubmit: "መልዕክት ላክ",
                alert: "እናመሰግናለን! መልዕክትዎ ደርሶናል።"
            },
            en: {
                navHome: "Home", navAbout: "About Us", navNews: "News", navContact: "Contact",
                headerTitle: "Get In Touch",
                headerSub: "Have questions or want to partner with us? We'd love to hear from you.",
                card1Title: "Our Office", card1Desc: "Addis Ketema Sub City<br>Addis Ababa, Ethiopia",
                card2Title: "Phone Number", card2Hours: "Mon - Fri, 8am - 6pm",
                card3Title: "Email Address",
                formTitle: "Send us a Message",
                lblFname: "First Name", lblLname: "Last Name", lblEmail: "Email Address", lblMsg: "Message",
                btnSubmit: "Send Message",
                alert: "Thank you! Your message has been sent to the team."
            }
        };

        function changeLanguage(lang) {
            // Navbar
            document.getElementById('nav-home').textContent = translations[lang].navHome;
            document.getElementById('nav-about').textContent = translations[lang].navAbout;
            document.getElementById('nav-news').textContent = translations[lang].navNews;
            document.getElementById('nav-contact').textContent = translations[lang].navContact;
            // Header & Content
            document.getElementById('header-title').textContent = translations[lang].headerTitle;
            document.getElementById('header-sub').textContent = translations[lang].headerSub;
            document.getElementById('card1-title').textContent = translations[lang].card1Title;
            document.getElementById('card1-desc').innerHTML = translations[lang].card1Desc;
            document.getElementById('card2-title').textContent = translations[lang].card2Title;
            document.getElementById('hours').textContent = translations[lang].card2Hours;
            document.getElementById('card3-title').textContent = translations[lang].card3Title;
            // Form
            document.getElementById('form-title').textContent = translations[lang].formTitle;
            document.getElementById('lbl-fname').textContent = translations[lang].lblFname;
            document.getElementById('lbl-lname').textContent = translations[lang].lblLname;
            document.getElementById('lbl-email').textContent = translations[lang].lblEmail;
            document.getElementById('lbl-msg').textContent = translations[lang].lblMsg;
            document.getElementById('btn-submit').textContent = translations[lang].btnSubmit;

            document.getElementById('btn-am').classList.toggle('active', lang === 'am');
            document.getElementById('btn-en').classList.toggle('active', lang === 'en');
            localStorage.setItem('preferredLang', lang);
        }

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const currentLang = localStorage.getItem('preferredLang') || 'am';
            alert(translations[currentLang].alert);
            this.reset();
        });

        window.onload = () => {
            const savedLang = localStorage.getItem('preferredLang') || 'am';
            changeLanguage(savedLang);
        };
    </script>
</body>
</html>