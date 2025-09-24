const CACHE_NAME = 'saddo-cache-v1';
const OFFLINE_URL = '/project/public/offline.html';

const PRECACHE_ASSETS = [
  '/project/public/',
  '/project/public/login',
  '/project/public/manifest.webmanifest',
  '/project/public/assets/styles.css',
  '/project/server/assets/styles.css',
  '/project/public/assets/pwa.js',
  '/project/public/images/logo.png',
  '/project/public/images/logo_saddo.png',
  OFFLINE_URL
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then(cache => cache.addAll(PRECACHE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames =>
      Promise.all(
        cacheNames
          .filter(cacheName => cacheName !== CACHE_NAME)
          .map(cacheName => caches.delete(cacheName))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          caches.open(CACHE_NAME).then(cache => cache.put(request, response.clone()));
          return response;
        })
        .catch(async () => {
          const cachedResponse = await caches.match(request);
          return cachedResponse || caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  if (request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(request).then(cachedResponse => {
      const fetchPromise = fetch(request)
        .then(networkResponse => {
          if (networkResponse && networkResponse.status === 200) {
            caches.open(CACHE_NAME).then(cache => cache.put(request, networkResponse.clone()));
          }
          return networkResponse;
        })
        .catch(() => cachedResponse);

      return cachedResponse || fetchPromise;
    })
  );
});