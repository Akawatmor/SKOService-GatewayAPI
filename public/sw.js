const STATIC_CACHE = 'gatewayapi-static-v2';
const RUNTIME_CACHE = 'gatewayapi-runtime-v2';
const APP_SHELL = [
    '/',
    '/search',
    '/offline.html',
    '/manifest.webmanifest',
    '/assets/css/app.css',
    '/assets/css/utilities.css',
    '/assets/js/app.js',
    '/assets/js/try-it-out.js',
    '/assets/icons/app-icon.svg',
    '/assets/icons/app-icon-192.png',
    '/assets/icons/app-icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(APP_SHELL)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys
            .filter((key) => ![STATIC_CACHE, RUNTIME_CACHE].includes(key))
            .map((key) => caches.delete(key))))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);
    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const response = await fetch(event.request);
                const cache = await caches.open(RUNTIME_CACHE);
                cache.put(event.request, response.clone());
                return response;
            } catch (error) {
                const cached = await caches.match(event.request);
                return cached || caches.match('/offline.html');
            }
        })());
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(RUNTIME_CACHE).then((cache) => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match('/offline.html'));
        })
    );
});