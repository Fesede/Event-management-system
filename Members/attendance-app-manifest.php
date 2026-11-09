<?php
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-cache');
echo json_encode([
    'id' => './attendance-app.php',
    'name' => 'St. Raguel Attendance',
    'short_name' => 'Attendance',
    'description' => 'Attendance Holder sign-in and member QR check-in.',
    'start_url' => './attendance-app.php',
    'scope' => './',
    'display' => 'standalone',
    'orientation' => 'portrait',
    'background_color' => '#f2f5f9',
    'theme_color' => '#173b6c',
    'icons' => [
        [
            'src' => 'attendance-app-icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => 'attendance-app-icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
    ],
], JSON_UNESCAPED_SLASHES);
