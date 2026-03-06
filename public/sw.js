// Cache version - bump when you want to invalidate all caches
const CACHE_VERSION = 'pbg-finance-v3';
// Only pre-cache static assets used for offline fallback (no HTML pages - those stay network-first)
const PRECACHE_ASSETS = [
  '/offline.html',
  '/pbg_logo_192.png',
  '/pbg_logo_512.png',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION)
      .then(cache => cache.addAll(PRECACHE_ASSETS.map(url => new Request(url, { cache: 'reload' }))))
      .then(() => self.skipWaiting())
      .catch(err => console.warn('SW install precache failed', err))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(key => key !== CACHE_VERSION).map(key => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
    return;
  }

  const isNavigation = event.request.mode === 'navigate' || event.request.destination === 'document';

  // HTML / navigation: always try network first so pages are never stale. Use cache only when offline.
  if (isNavigation) {
    event.respondWith(
      fetch(event.request)
        .then(response => response)
        .catch(() => caches.match(event.request).then(r => r || caches.match('/offline.html')))
    );
    return;
  }

  // Static assets (JS, CSS, images, fonts): cache-first for speed; URLs are versioned by build so no stale assets
  const url = new URL(event.request.url);
  const isStaticAsset = /\.(js|css|woff2?|png|jpg|jpeg|gif|ico|svg|webp)(\?.*)?$/i.test(url.pathname)
    || url.pathname.startsWith('/build/');

  if (isStaticAsset) {
    event.respondWith(
      caches.match(event.request).then(cached => {
        if (cached) return cached;
        return fetch(event.request).then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_VERSION).then(cache => cache.put(event.request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // Other same-origin GET (e.g. API): network first
  event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
