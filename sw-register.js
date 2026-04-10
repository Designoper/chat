const ROOT = "/";

if ("serviceWorker" in navigator) {
	navigator.serviceWorker.register(`${ROOT}sw.js`)
		.then(() => console.log("Service Worker registrado"))
		.catch(err => console.error("Error al registrar SW:", err));
}
