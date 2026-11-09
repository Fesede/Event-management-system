'use strict';

const CACHE_NAME = 'st-raguel-attendance-v2';
const APP_ASSETS = [
    'attendance-app.css',
    'attendance-app.js',
    'attendance-app-icon-192.png',
    'attendance-app-icon-512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(APP_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(names => Promise.all(
                names
                    .filter(name => name.startsWith('st-raguel-attendance-') && name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const requestUrl = new URL(event.request.url);
    if (event.request.method !== 'GET' || requestUrl.origin !== self.location.origin) return;

    const asset = APP_ASSETS.find(path => new URL(path, self.registration.scope).href === requestUrl.href);
    if (!asset) return;

    event.respondWith(
        caches.match(event.request).then(cached => cached || fetch(event.request))
    );
});
