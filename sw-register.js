const PATHNAME = "/";
const FILENAME = "sw.js";

if (navigator.serviceWorker) {
	try {
		await navigator.serviceWorker.register(`${PATHNAME}${FILENAME}`, { type: 'module' });
	} catch (err) {
		console.error("Error al registrar SW:", err);
	}
}
