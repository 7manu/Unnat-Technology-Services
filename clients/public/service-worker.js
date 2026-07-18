const CACHE_NAME = 'uts-leads-v1';
const ASSETS = [
  '/',
  '/login',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/img/logo-uts.webp',
  '/favicon.webp',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  event.respondWith(fetch(event.request).catch(() => caches.match(event.request).then((response) => response || caches.match('/login'))));
});

self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};
  event.waitUntil(self.registration.showNotification(data.title || 'Meeting reminder', {
    body: data.body || 'A client meeting is coming up soon.',
    icon: '/assets/img/logo-uts.webp',
    badge: '/assets/img/logo-uts.webp',
    data: { url: data.url || '/projects' }
  }));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data.url || '/projects'));
});
