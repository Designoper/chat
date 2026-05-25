import Mensaje from "./Mensaje.js";

export default class Conexion extends Mensaje {
	urlStreamConexion = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);

	constructor() {
		super();
	}

	// MARK: STREAM CONEXION
	streamConexion() {

		this.urlStreamConexion.search = this.params;
		const evtSource = new EventSource(this.urlStreamConexion);

		evtSource.addEventListener("ping", async () => {
			await this.fetchWithoutForm(this.ENDPOINTS.POST.CONEXION.ESTADO, 'post', this.paramsObj);
			console.log(this.paramsObj);
		});

		evtSource.addEventListener("initial state", (event) => {
			const state = JSON.parse(event.data);
			console.log(state);
			this.circle = document.querySelector('circle');
			this.circle.setAttribute('class', state.estado);
		});

		evtSource.addEventListener("cambio", (event) => {
			const state = JSON.parse(event.data);
			document.querySelector('circle').setAttribute('class', state.estado);
		});
	}
}
