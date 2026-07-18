var staticCaches = ["mobirise-cache-v1"];
var offlinePage = "offline.html"; // Add the offline page

function inArray(a, c) {
  return (
    a.filter(function (b) {
      return b === c;
    }).length > 0
  );
}

self.addEventListener("install", function (event) {
  console.log("SW: Installed and updated");
  event.waitUntil(
    caches.open(staticCaches).then(function (cache) {
      console.log("SW: Caching offline page");
      return cache.addAll([
        "/",
        "manifest.json",
        offlinePage, // Cache the offline page during installation
      ]);
    })
  );
  self.skipWaiting();
});

self.addEventListener("activate", function (event) {
  console.log("SW: Activate");
  event.waitUntil(
    caches.keys().then(function (cacheNames) {
      return Promise.all(
        cacheNames.map(function (cacheName) {
          if (!inArray(staticCaches, cacheName)) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener("fetch", function (event) {
  // Handle fetch events for HTTP requests
  if (event.request.url.startsWith("http")) {
    event.respondWith(
      fetch(event.request)
        .then(function (response) {
          if (response.status === 404) {
            return new Response("Page not found!");
          }

          var clonedResponse = response.clone();
          caches.open(staticCaches).then(function (cache) {
            cache
              .matchAll(event.request, { ignoreSearch: true })
              .then(function (matches) {
                return Promise.all(
                  matches.map(function (match) {
                    return cache.delete(match);
                  })
                );
              })
              .then(function () {
                cache.put(event.request, clonedResponse);
              });
          });
          return response;
        })
        .catch(function () {
          console.log("Offline mode. Serving cached content or offline page.");
          return caches.match(event.request).then(function (cachedResponse) {
            return cachedResponse || caches.match(offlinePage); // Serve offline.html if no cache is found
          });
        })
    );
  }
});
