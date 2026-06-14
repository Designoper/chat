import Mensaje from "./Mensaje.js";

export default class Conexion extends Mensaje {
	urlStreamConexion = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);

	constructor() {
		super();
	}

	// MARK: STREAM CONEXION
	streamConexion() {

		this.urlStreamConexion.search = this.urlSearchparams;
		const evtSource = new EventSource(this.urlStreamConexion);

		evtSource.addEventListener("keepalive", async () => {
			await this.fetchWithoutForm(this.ENDPOINTS.POST.CONEXION.ESTADO, 'post', this.paramsObj);
		});

		evtSource.addEventListener("conexion directo", (event) => {
			const state = JSON.parse(event.data);
			let ultimaConexion = state[0].estado;

			if (ultimaConexion === '0') {
				ultimaConexion = 'Sin conexión';
			}

			if (ultimaConexion !== 'Online' && ultimaConexion !== 'Sin conexión') {
				ultimaConexion = `Última conexión: ${this.fullDate(ultimaConexion)}`;
			}

			document.querySelector('header p').innerHTML = ultimaConexion;
		});
	}
}
