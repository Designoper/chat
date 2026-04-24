const PATHNAME = "/";
const FILENAME = "sw-empty.js";

(async () => {
	if (navigator.serviceWorker) {
		try {
			await navigator.serviceWorker.register(`${PATHNAME}${FILENAME}`);
		} catch (err) {
			console.error("Error al registrar SW:", err);
		}
	}
})();
