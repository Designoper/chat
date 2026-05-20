import Mensaje from "./Mensaje.js";

export default class Conexion extends Mensaje {
	urlStreamConexion = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
	endpointConexion = this.ENDPOINTS.POST.CONEXION.ESTADO;
	header = document.querySelector('header');

	conexionData = {};

	constructor() {
		super();
	}

	streamConexion(urlStream) {
		const evtSource = new EventSource(urlStream);

		// evtSource.addEventListener("ping", async () => {
		// 	await this.fetchWithoutForm(this.endpointConexion, 'post', this.conexionData);
		// });

		evtSource.addEventListener("initial state", (event) => {
			const state = event.data;
			this.header.insertAdjacentHTML('beforeend', `<svg viewBox="0 0 100 100">
				<circle cx="50" cy="50" r="50" />
			</svg>`);
			this.circle = document.querySelector('circle');
			this.circle.setAttribute('class', state);
		});

		evtSource.addEventListener("cambio", (event) => {
			const state = event.data;
			this.circle.setAttribute('class', state);
		});
	}
}
