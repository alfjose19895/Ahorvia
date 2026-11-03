const CACHE_NAME = 'ahorvia-shell-v1';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/favicon.svg',
    '/manifest.webmanifest',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Nunca almacenar respuestas autenticadas, endpoints Livewire ni rutas dinámicas financieras.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    const isStaticAsset =
        url.origin === self.location.origin &&
        (url.pathname.startsWith('/build/assets/') ||
            url.pathname.startsWith('/icons/') ||
            url.pathname === '/favicon.svg' ||
            url.pathname === '/manifest.webmanifest');

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then(
                (cached) =>
                    cached ||
                    fetch(request).then((response) => {
                        if (response.ok) {
                            const clone = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                        }
                        return response;
                    })
            )
        );
    }
});

