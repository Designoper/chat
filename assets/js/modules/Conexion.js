import Mensaje from "./Mensaje.js";

export default class Conexion extends Mensaje {
	urlStreamConexion = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
	endpointConexion = this.ENDPOINTS.POST.CONEXION.ESTADO;
	span = document.querySelector('span');
	circle = document.querySelector('circle');

	conexionData = {};

	constructor() {
		super();
	}

	streamConexion(urlStream) {
		const evtSource = new EventSource(urlStream);

		evtSource.addEventListener("ping", async () => {
			await this.fetchWithoutForm(this.endpointConexion, 'post', this.conexionData);
		});

		evtSource.addEventListener("initial state", (event) => {
			const state = event.data;
			this.circle.setAttribute('class', state);
		});

		evtSource.addEventListener("cambio", (event) => {
			const state = event.data;
			this.circle.setAttribute('class', state);
		});
	}
}
