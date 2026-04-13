const CACHE_NAME = "static-v1";

// Archivos estáticos que quieres disponibles offline
const STATIC_ASSETS = [
	"/",            // tu index.html
	"/styles.css",
	"/app.js",
	"/icon.png",
	// agrega aquí más archivos estáticos si quieres
];

// Instalación: cachear solo archivos estáticos
self.addEventListener("install", event => {
	event.waitUntil(
		caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
	);
	self.skipWaiting();
});

// Activación: limpiar caches antiguos
self.addEventListener("activate", event => {
	event.waitUntil(
		caches.keys().then(keys =>
			Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))
		)
	);
	self.clients.claim();
});

// Fetch: NO interceptar APIs ni endpoints dinámicos
self.addEventListener("fetch", event => {
	const url = new URL(event.request.url);

	// ❌ No interceptar tus APIs
	if (
		url.pathname.startsWith("/api/mensajes") ||
		url.pathname.startsWith("/api/login") ||
		url.pathname.startsWith("/api/usuarios")
	) {
		return; // dejar que el navegador haga fetch normal
	}

	// ❌ No interceptar POST, PUT, DELETE, etc.
	if (event.request.method !== "GET") {
		return;
	}

	// ✔️ Cache-first SOLO para archivos estáticos
	event.respondWith(
		caches.match(event.request).then(cached => {
			if (cached) return cached;
			return fetch(event.request);
		})
	);
});
