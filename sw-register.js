const PATHNAME = "/";
const FILENAME = "sw-uninstall.js";

if ("serviceWorker" in navigator) {
	navigator.serviceWorker.register(`${PATHNAME}${FILENAME}`)
		.then(() => console.log("Service Worker registrado"))
		.catch(err => console.error("Error al registrar SW:", err));
}
