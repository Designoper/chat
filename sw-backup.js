const VERSION = 13;

// Instalación: activar inmediatamente
self.addEventListener("install", event => {
	self.skipWaiting();
});

// Activación: limpiar caches antiguos
self.addEventListener("activate", event => {
	event.waitUntil(
		caches.keys().then(keys =>
			Promise.all(keys.filter(key => key !== VERSION).map(key => caches.delete(key)))
		)
	);
	self.clients.claim();
});

// Fetch
self.addEventListener("fetch", event => {
	const request = event.request;

	if (request.method !== "GET") return;

	// Network-first para HTML
	if (request.headers.get("accept").includes("text/html")) {
		event.respondWith(
			fetch(request, { cache: "no-store" })
				.then(response => {
					// Guardamos una copia en caché por si hay offline
					caches.open(VERSION).then(cache => cache.put(request, response.clone()));
					return response;
				})
				.catch(() => caches.match(request))
		);
		return;
	}

	// Cache-first para el resto
	event.respondWith(
		caches.match(request).then(cached => {
			if (cached) return cached;

			return fetch(request).then(response => {
				if (response && response.status === 200 && response.type === "basic") {
					const cloned = response.clone();
					caches.open(VERSION).then(cache => cache.put(request, cloned));
				}
				return response;
			});
		})
	);
});
