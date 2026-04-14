self.addEventListener('install', () => {
	self.skipWaiting();
});

self.addEventListener('activate', () => {
	self.registration.unregister();
	clients.matchAll().then(clients => {
		clients.forEach(client => client.navigate(client.url));
	});
});
