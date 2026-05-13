import Usuario from "./Usuario.js";

export default class Conexion extends Usuario {

	constructor() {
		super();

		// Inicializaciones
		this.urlStream = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
		this.endpointConexion = new URL(this.ENDPOINTS.POST.CONEXION.ESTADO);
	}

	streamConexion() {
		const evtSource = new EventSource(this.urlStream);

		// Cuando se abre la conexión SSE → marcar conectado
		evtSource.onopen = async () => {
			await this.fetchWithoutForm(this.endpointConexion, 'post');
		};

		// El servidor envía "ping" cada 15s → actualizamos last_seen
		evtSource.addEventListener("ping", async () => {
			await this.fetchWithoutForm(this.endpointConexion, 'post');
		});
	}
}
