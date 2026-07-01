const CACHE_NAME = "pojok-cafe-v1";

const urlsToCache = [
  "/pojok_cafe/",
  "/pojok_cafe/login/login.php",
  "/pojok_cafe/karyawan/dashboard.php",
  "/pojok_cafe/owner/dashboard.php",
  "/pojok_cafe/offline/offline.html"
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener("fetch", event => {
  event.respondWith(
    fetch(event.request)
      .catch(() =>
        caches.match(event.request).then(cached => {
          if (cached) return cached;
          if (event.request.mode === "navigate") {
            return caches.match("/pojok_cafe/offline/offline.html");
          }
        })
      )
  );
});