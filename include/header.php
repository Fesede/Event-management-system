<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>St.Raguel Church</title>
    <link rel="icon" type="image/png" href="assets/image/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Font for Ethiopic Text -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/style.css">
</head>
<body>

<!-- NAVIGATION -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="assets/image/logo.png" alt="EventHub Logo" class="img-fluid" style="max-height: 40px;">
             St. Raguel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php" id="nav-home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php" id="nav-about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="News.php" id="nav-news">News</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php" id="nav-events">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php" id="nav-contact">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php" id="nav-contact">Login</a></li>
                <!-- LANGUAGE TOGGLE -->
                <li class="nav-item ms-lg-3">
                    <button class="lang-btn" id="btn-am" onclick="changeLanguage('am')">አማርኛ</button>
                    <button class="lang-btn active" id="btn-en" onclick="changeLanguage('en')">EN</button>
                </li>
            </ul>
        </div>
    </div>
</nav>