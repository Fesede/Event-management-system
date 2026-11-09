# Attendance phone app

The Attendance phone app is an installable web app for Attendance Holders. It replaces the native Android project and works from the website on Android and iPhone.

## Setup

1. Deploy the website on an HTTPS URL. Camera access and the attendance API require HTTPS.
2. Apply `database/attendance_migration.sql` and `database/attendance_api_migration.sql` to the website's `st_raguel` database if they have not already been applied.
3. Give the Attendance Holder an active account and assign at least one open session.
4. Open `Members/attendance-app.php` in a supported mobile browser and sign in with the holder's email and password.
5. Install from the browser's install prompt or menu. On iPhone, use **Share → Add to Home Screen**.

Choose an assigned session to view its date, location, check-in window, and attendance counts. The assigned holder can search the session roster and display an existing member attendance QR code. Tap **Start camera** to scan a member's code. The app sends scans to the existing attendance API, which checks the session assignment, check-in window, and member roster and writes the attendance record and audit log.

The sign-in token is kept in the current browser tab's session storage and removed from the device when the holder signs out. The app attempts to revoke the server token on sign-out; if the request cannot reach the server, that token remains valid until it expires. Attendance check-ins require an active internet connection; the service worker only caches the app's static shell and never caches API requests.
