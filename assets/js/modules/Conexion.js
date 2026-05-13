import Usuario from "./Usuario.js";

export default class Conexion extends Usuario {

    constructor() {
        super();

        // Inicializaciones
        this.urlStream = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
        this.endpointConexion = new URL(this.ENDPOINTS.POST.CONEXION.ESTADO);
        this.url = new URL(location.href);

        this.id_receptor = this.url.searchParams.get('id-receptor');
        this.nombre_receptor = this.url.searchParams.get('nombre-receptor');
        this.id_grupo = this.url.searchParams.get('id-grupo');
        this.nombre_grupo = this.url.searchParams.get('nombre-grupo');

        this.input = document.querySelector('input[type="hidden"]');

        // Iniciar SSE + presencia
        // this.iniciarStream();
    }

    // async setConexion() {
    //     await this.fetchWithoutForm(this.endpointConexion, 'post');
    // }

    streamConexion() {
        const evtSource = new EventSource(this.urlStream);

        // El servidor envía "ping" cada 15s → actualizamos last_seen
        evtSource.addEventListener("ping", async () => {
            await this.enviarHeartbeat();
        });

        // Cuando se abre la conexión SSE → marcar conectado
        evtSource.onopen = async () => {
            await this.enviarHeartbeat();
        };

        // Si la conexión SSE se cae → marcar desconectado
        // evtSource.onerror = async () => {
        //    await this.enviarDesconexion();
        // };
    }

    async enviarHeartbeat() {
        await this.fetchWithoutForm(this.endpointConexion, 'post');
    }

    // async enviarDesconexion() {
    //     await this.fetchWithoutForm(this.endpointConexion, 'post');
    // }
}
