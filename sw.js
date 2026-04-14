const VERSION = 14;

// Instalación: activar inmediatamente
self.addEventListener("install", event => {
	self.skipWaiting();
});

// Activación: limpiar caches antiguos + activar navigation preload
self.addEventListener("activate", event => {
	event.waitUntil(
		(async () => {
			// Limpiar caches viejos
			const keys = await caches.keys();
			await Promise.all(keys.filter(key => key !== VERSION).map(key => caches.delete(key)));

			// Activar navigation preload (evita el error del body usado)
			if (self.registration.navigationPreload) {
				await self.registration.navigationPreload.enable();
			}
		})()
	);

	self.clients.claim();
});

// Fetch
self.addEventListener("fetch", event => {
	const request = event.request;
	const url = new URL(request.url);

	// ❌ No interceptar tus APIs
	if (url.pathname.startsWith("/api/")) {
		return;
	}

	// Network-first para HTML con navigation preload
	if (request.headers.get("accept")?.includes("text/html")) {
		event.respondWith((async () => {
			try {
				// Usar preload si existe (evita el error del body consumido)
				const preload = await event.preloadResponse;
				const response = preload || await fetch(request, { cache: "no-store" });

				// Guardar copia en caché
				const cache = await caches.open(VERSION);
				cache.put(request, response.clone());

				return response;
			} catch (err) {
				// Offline → usar caché
				return caches.match(request);
			}
		})());
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
