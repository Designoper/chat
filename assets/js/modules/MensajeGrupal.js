import Mensaje from "./Mensaje.js";
import formatearFecha from "../utils/fecha.js";

export default class MensajeGrupal extends Mensaje {
	h1 = document.querySelector('h1');
	input = document.querySelector('input[type="hidden"]');
	id_grupo = new URL(location.href).searchParams.get('id-grupo');
	nombre_grupo = new URL(location.href).searchParams.get('nombre-grupo');

	constructor() {
		super();
	}

	streamMensajesGrupales() {
		const evtSource = new EventSource(`${this.ENDPOINTS.STREAM_MENSAJES_GRUPALES}?ultimo_id=${this.ultimoId}&id_grupo=${this.id_grupo}`);

		evtSource.addEventListener("mensaje", (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);
			this.MENSAJES_OUTPUT.insertAdjacentHTML("beforeend", content);
			this.formHandler();

			this.ultimoId = mensaje.id_mensaje;
		});

		evtSource.addEventListener("ping", async (event) => {
			const form = new FormData();
			form.append("id_grupo", this.id_grupo);

			await fetch(this.ENDPOINTS.ULTIMA_CONEXION_GRUPAL, {
				method: "POST",
				body: form
			});
		});
	}

	writeChat() {
		this.h1.innerHTML = `${this.nombre_grupo}`;
		this.input.setAttribute('value', `${this.id_grupo}`);
	}

	async writeMensajeGrupal(form, method, action) {
		await this.fetchData(form, method, action);
	}
}