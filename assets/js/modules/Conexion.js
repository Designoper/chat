import Mensaje from "./Mensaje.js";
// import Usuario from "./Usuario.js";

export default class Conexion extends Mensaje {
	urlStreamConexion = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
	endpointConexion = this.ENDPOINTS.POST.CONEXION.ESTADO;

	conexionData = {};

	constructor() {
		super();
	}

	streamConexion(urlStream) {
		const evtSource = new EventSource(urlStream);

		// Cuando se abre la conexión SSE → marcar conectado
		evtSource.onopen = async () => {
			await this.fetchWithoutForm(this.endpointConexion, 'post', this.conexionData);
		};

		// El servidor envía "ping" cada 15s → actualizamos last_seen
		evtSource.addEventListener("ping", async () => {
			await this.fetchWithoutForm(this.endpointConexion, 'post', this.conexionData);
		});
	}
}
