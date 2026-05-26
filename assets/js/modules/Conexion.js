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
		});

		evtSource.addEventListener("conexion directo", (event) => {
			const state = JSON.parse(event.data);
			document.querySelector('circle').setAttribute('class', state[0].estado);
		});
	}
}
