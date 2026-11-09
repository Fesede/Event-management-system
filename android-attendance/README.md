# St. Raguel Attendance for Android

Native Android app for Attendance Holders to sign in, view assigned sessions, and scan member QR codes.

## Backend setup

1. Deploy the website on an HTTPS URL. The app deliberately refuses HTTP because sign-in credentials and bearer tokens must be encrypted in transit.
2. Apply `Members/database/attendance_api_migration.sql` once to the same `st_raguel` database used by the website. It creates the hashed API-token table.
3. Ensure the Holder has an active account, an assigned session, and that the session's check-in window is open.

The API is under `Members/api/`. API tokens expire after 30 days and are stored hashed in the database. The Android app encrypts the token with Android Keystore and only issues attendance scans through the existing roster, window, role, and audit checks.

## Build the APK

1. Install Android Studio with JDK 17 and Android SDK Platform 35.
2. Open the `android-attendance` directory in Android Studio and allow Gradle sync to finish.
3. Choose **Build → Build APK(s)**. Android Studio outputs the debug APK under `app/build/outputs/apk/debug/`.
4. Install the APK on an Android phone. Enter the website HTTPS base URL (including the project folder, if any), then sign in with an active Attendance Holder account.
5. Grant camera permission, select an assigned session, and scan member QR cards.

Sessions must be created and assigned by an Attendance Controller on the website. Android QR scans are recorded in the same `attendance_records` and `attendance_audit_log` tables as web check-ins.
