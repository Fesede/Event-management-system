<?php
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(self)');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; connect-src 'self'; media-src 'self' blob:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#173b6c">
    <meta name="description" content="Sign in to your assigned attendance sessions and scan member QR codes.">
    <link rel="manifest" href="attendance-app-manifest.php">
    <link rel="icon" href="attendance-app-icon-192.png" type="image/png">
    <link rel="apple-touch-icon" href="attendance-app-icon-192.png">
    <link rel="stylesheet" href="attendance-app.css">
    <title>St. Raguel Attendance</title>
</head>
<body>
    <main class="app-shell">
        <header class="app-header">
            <img class="app-logo" src="attendance-app-icon-192.png" alt="">
            <div class="brand-copy">
                <p class="eyebrow">ST. RAGUEL CHURCH</p>
                <h1>Attendance</h1>
            </div>
            <button class="button button-quiet install-button" id="install-button" type="button" hidden>Install app</button>
        </header>

        <p class="app-status" id="app-status" role="status" aria-live="polite">Sign in with your Attendance Holder account.</p>

        <section class="panel login-panel" id="login-panel" aria-labelledby="login-title">
            <div class="panel-heading">
                <p class="eyebrow">ATTENDANCE HOLDER</p>
                <h2 id="login-title">Welcome</h2>
                <p>Sign in to view your assigned sessions and check in members.</p>
            </div>
            <form id="login-form" autocomplete="on">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" autocomplete="username" inputmode="email" required>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <button class="button button-primary button-full" id="login-button" type="submit">Sign in</button>
            </form>
            <p class="install-hint">Install this app from your browser menu. On iPhone, tap Share, then Add to Home Screen.</p>
        </section>

        <section class="panel attendance-panel" id="attendance-panel" aria-labelledby="sessions-title" hidden>
            <div class="panel-heading panel-heading-row">
                <div>
                    <p class="eyebrow">SIGNED IN AS</p>
                    <h2 id="signed-in-name">Attendance Holder</h2>
                </div>
                <button class="button button-quiet" id="sign-out-button" type="button">Sign out</button>
            </div>

            <div class="section-heading">
                <div>
                    <h3 id="sessions-title">Your sessions</h3>
                    <p>Select the session you are checking in.</p>
                </div>
                <button class="button button-quiet" id="refresh-button" type="button">Refresh</button>
            </div>

            <label for="session-select">Assigned session</label>
            <select id="session-select" name="session" disabled>
                <option value="">Loading sessions…</option>
            </select>

            <div class="session-summary" id="session-summary" hidden>
                <p class="session-title" id="session-title"></p>
                <p id="session-details"></p>
                <p class="session-window" id="session-window"></p>
                <div class="attendance-counts" id="attendance-counts" hidden>
                    <span><strong id="present-count">0</strong><span>Present</span></span>
                    <span><strong id="excused-count">0</strong><span>Excused</span></span>
                    <span><strong id="absent-count">0</strong><span>Absent</span></span>
                    <span><strong id="roster-count">0</strong><span>On roster</span></span>
                </div>
            </div>

            <section class="roster-panel" id="roster-panel" aria-labelledby="roster-title" hidden>
                <div class="section-heading">
                    <div>
                        <h3 id="roster-title">Member attendance QR codes</h3>
                        <p>Show a member their code or search the roster.</p>
                    </div>
                </div>
                <label for="roster-search">Search members</label>
                <input id="roster-search" type="search" placeholder="Search by name" autocomplete="off">
                <div class="member-list" id="member-list" aria-live="polite"></div>
            </section>

            <div class="scanner-panel" id="scanner-panel" hidden>
                <div class="scanner-heading">
                    <div>
                        <h3>Member QR code</h3>
                        <p>Center the member’s QR code in the camera.</p>
                    </div>
                    <span class="camera-mark" aria-hidden="true">QR</span>
                </div>
                <div id="qr-reader" class="qr-reader" aria-label="QR code camera preview" hidden></div>
                <button class="button button-primary button-full" id="start-scan-button" type="button">Start camera</button>
                <button class="button button-quiet button-full" id="stop-scan-button" type="button" hidden>Stop camera</button>
            </div>
        </section>

        <footer class="app-footer">
            <span>Check-ins require an internet connection.</span>
            <span>Camera access requires HTTPS.</span>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js" integrity="sha384-c9d8RFSL+u3exBOJ4Yp3HUJXS4znl9f+z66d1y54ig+ea249SpqR+w1wyvXz/lk+" crossorigin="anonymous" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js" integrity="sha384-3zSEDfvllQohrq0PHL1fOXJuC/jSOO34H46t6UQfobFOmxE5BpjjaIJY5F2/bMnU" crossorigin="anonymous" defer></script>
    <script src="attendance-app.js" defer></script>
</body>
</html>
