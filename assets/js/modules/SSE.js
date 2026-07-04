// 🌟 Módulo genérico para gestionar SSE + REST + reconexión + duplicados
export default class SSEManager {

	constructor({
		restLoader,          // función REST que carga el estado completo
		sseUrl,              // URL del SSE
		eventName,           // nombre del evento SSE
		onRestData,          // callback para pintar datos REST
		onSseData,           // callback para pintar datos SSE
		getLastIdFromRest,   // función que extrae el último ID del REST
		getIdFromSse         // función que extrae el ID del evento SSE
	}) {

		this.restLoader = restLoader;
		this.sseUrl = sseUrl;
		this.eventName = eventName;
		this.onRestData = onRestData;
		this.onSseData = onSseData;
		this.getLastIdFromRest = getLastIdFromRest;
		this.getIdFromSse = getIdFromSse;

		this.source = null;
		this.lastId = null;

		document.addEventListener("visibilitychange", () => {
			if (document.visibilityState === "visible") {
				this.resume();
			}
		});
	}

	// 🌟 Recargar estado + reiniciar SSE
	async resume() {

		// 1. Cerrar SSE si está en estado raro
		if (this.source && this.source.readyState !== 1) {
			this.source.close();
			this.source = null;
		}

		// 2. Recargar estado por REST
		const restData = await this.restLoader();
		this.onRestData(restData);

		// 3. Actualizar último ID
		this.lastId = this.getLastIdFromRest(restData);

		// 4. Reiniciar SSE limpio
		this.start();
	}

	// 🌟 Iniciar SSE
	start() {

		this.source = new EventSource(this.sseUrl);

		this.source.addEventListener(this.eventName, (event) => {
			const data = JSON.parse(event.data);
			const id = this.getIdFromSse(data);

			// Evitar duplicados
			if (this.lastId !== null && id <= this.lastId) {
				return;
			}

			this.lastId = id;
			this.onSseData(data);
		});

		this.source.onerror = () => {
			console.log(`SSE ${this.eventName} reconectando...`);
		};
	}

	// 🌟 Cerrar SSE manualmente
	stop() {
		if (this.source) {
			this.source.close();
			this.source = null;
		}
	}
}
