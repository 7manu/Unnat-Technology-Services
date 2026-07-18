const CACHE_NAME = 'asset-manager-v1';
const ASSETS = [
  '/Assets/',
  '/Assets/dashboard.php',
  '/Assets/index.php',
  '/Assets/assets/css/style.css',
  '/Assets/assets/js/script.js',
  '/Assets/pwa/manifest.json',
  '/Assets/404.html',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://code.jquery.com/jquery-3.7.0.min.js'
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS);
    })
  );
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) return caches.delete(key);
        })
      );
    })
  );
});

self.addEventListener("fetch", event => {
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request).then(res => {
        return res || caches.match('/Assets/404.html');
      });
    })
  );
});
self.addEventListener('sync', event => {
  if (event.tag === 'sync-assets') {
    event.waitUntil(syncAssetsToServer());
  }
});

async function syncAssetsToServer() {
  const db = await openAssetDB();
  const tx = db.transaction('pendingAssets', 'readonly');
  const store = tx.objectStore('pendingAssets');
  const all = await store.getAll();

  for (const asset of all) {
    try {
      const res = await fetch('/Assets/backend/add_asset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(asset).toString()
      });

      if (res.ok) {
        // Delete after successful sync
        const deleteTx = db.transaction('pendingAssets', 'readwrite');
        deleteTx.objectStore('pendingAssets').delete(asset.id);
      }
    } catch (err) {
      console.error("Sync failed:", err);
    }
  }
}

function openAssetDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open('AssetDB', 1);
    req.onerror = () => reject("DB failed");
    req.onsuccess = () => resolve(req.result);
    req.onupgradeneeded = e => {
      const db = e.target.result;
      db.createObjectStore('pendingAssets', { keyPath: 'id', autoIncrement: true });
    };
  });
}
