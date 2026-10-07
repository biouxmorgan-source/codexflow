// Service worker de CodexFlow : application installable, fiche du personnage lisible hors ligne,
// notifications push. Seules les réponses marquées X-Codexflow-Offline sont gardées, et ce cache
// est vidé dès qu'une page de connexion s'affiche (déconnexion, session expirée).
const STATIC = 'codexflow-static-v1';
const PAGES = 'codexflow-pages';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC)
            .then((cache) => cache.addAll([OFFLINE_URL, '/icons/icon-192.png', '/favicon.ico']))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key.startsWith('codexflow-static-') && key !== STATIC).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'clear-pages') {
        event.waitUntil(caches.delete(PAGES));
    }
});

const isStatic = (url) => url.pathname.startsWith('/build/')
    || url.pathname.startsWith('/icons/')
    || /^\/livewire[^/]*\/livewire.*\.js$/.test(url.pathname)
    || url.pathname === '/favicon.ico';

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || request.headers.has('range')) {
        return;
    }

    if (isStatic(url)) {
        event.respondWith(cacheFirst(request));
    } else if (!url.pathname.startsWith('/broadcasting') && !url.pathname.startsWith('/livewire')) {
        event.respondWith(networkFirst(request));
    }
});

// Fichiers compilés (noms versionnés) : le cache d'abord.
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(STATIC);
        cache.put(request, response.clone());
    }

    return response;
}

// Pages : le réseau d'abord, la dernière version gardée si l'appareil est hors ligne.
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.status === 200 && !response.redirected && response.headers.get('X-Codexflow-Offline') === '1') {
            const cache = await caches.open(PAGES);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cache = await caches.open(PAGES);
        const cached = await cache.match(request) ?? await cache.match(request, { ignoreSearch: true });
        if (cached) return cached;

        if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
            return caches.match(OFFLINE_URL);
        }

        throw error;
    }
}

self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? {};

    event.waitUntil(self.registration.showNotification(data.title ?? 'CodexFlow', {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        tag: data.tag,
        renotify: data.renotify ?? false,
        lang: data.lang,
        data: data.data ?? {},
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = new URL(event.notification.data?.url ?? '/', self.location.origin).href;

    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const client = windows.find((candidate) => new URL(candidate.url).origin === self.location.origin);
        if (client) {
            await client.focus();
            return client.navigate(url);
        }

        return self.clients.openWindow(url);
    })());
});
