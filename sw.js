const CACHE_NAME = "uts-public-v9";
const OFFLINE_URL = "offline.html";
const CORE_ASSETS = [
  "./",
  "contact.html",
  OFFLINE_URL,
  "manifest.json",
  "assets/theme/css/uts-modern.css",
  "assets/theme/js/uts-modern.js",
  "assets/theme/js/uts-assistant.js",
  "assets/images/uts-logo-removebg-removebg-preview-512x512.webp",
  "assets/images/linkedin.svg",
  "assets/images/instagram.svg",
  "assets/images/facebook.svg",
  "assets/images/whatsapp.svg",
];

self.addEventListener("install", (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS)));
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((names) => Promise.all(names.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET" || !request.url.startsWith(self.location.origin)) return;

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.ok) caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone()));
          return response;
        })
        .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL)))
    );
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) return cached;
      return fetch(request).then((response) => {
        if (response.ok && ["style", "script", "image", "font"].includes(request.destination)) {
          caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone()));
        }
        return response;
      });
    })
  );
});
