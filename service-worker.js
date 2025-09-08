const cacheVersion = 1.0;

const files = [
	'./manifest.json',
	'./index.html',
	'./assets/css/common/reset.css',
	'./assets/css/common/colors.css',
	'./assets/css/common/main-layout.css',
	'./assets/css/common/text.css',
	'./assets/css/index.css',
	'./assets/js/Fetch.js',
	'./assets/js/Categoria.js',
	'./assets/js/Libro.js',
];

// Evento de instalación: cachear archivos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(cacheVersion).then(cache => {
            console.log('Archivos cacheados');
            return cache.addAll(files);
        })
    );
});

// Evento de fetch: servir archivos desde la caché o desde la red
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});

// Evento de activación: limpiar cachés antiguas
self.addEventListener('activate', event => {
    const allowedCaches = [cacheVersion];
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (!allowedCaches.includes(key)) {
                        console.log('Caché antiguo eliminado:', key);
                        return caches.delete(key);
                    }
                })
            );
        })
    );
});
