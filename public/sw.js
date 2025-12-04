const CACHE_NAME = 'pbg-finance-v2';
const ASSETS_TO_CACHE = [
  '/',
  '/pbg_logo_192.png',
  '/pbg_logo_512.png',
  '/manifest.json'
  // Add other essential static assets
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache opened');
        // Only cache existing files
        return Promise.all(
          ASSETS_TO_CACHE.map(asset => {
            return cache.add(asset).catch(e => {
              console.log(`Skipping ${asset}`, e);
            });
          })
        );
      })
  );
});

self.addEventListener('fetch', (event) => {
  // Skip non-GET requests and external URLs
  if (event.request.method !== 'GET' || 
      !event.request.url.startsWith(self.location.origin)) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        // Return cached response if found
        if (cachedResponse) return cachedResponse;
        
        // Otherwise fetch from network
        return fetch(event.request)
          .catch(() => {
            // If offline and navigation request, show offline page
            if (event.request.mode === 'navigate') {
              return caches.match('/offline.html');
            }
            return new Response('Offline - no cached content');
          });
      })
  );
});