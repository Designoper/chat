const PATHNAME = "/";

if ("serviceWorker" in navigator) {
	navigator.serviceWorker.register(`${PATHNAME}sw.js`)
		.then(() => console.log("Service Worker registrado"))
		.catch(err => console.error("Error al registrar SW:", err));
}
