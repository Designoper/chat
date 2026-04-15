const PATHNAME = "/";

if ("serviceWorker" in navigator) {
	navigator.serviceWorker.register(`${PATHNAME}sw-uninstall.js`)
		.then(() => console.log("Service Worker registrado"))
		.catch(err => console.error("Error al registrar SW:", err));
}
