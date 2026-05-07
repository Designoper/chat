import Mensaje from "./Mensaje.js";
import formatearFecha from "../utils/fecha.js";

export default class MensajeDirecto extends Mensaje {
	h1 = document.querySelector('h1');
	input = document.querySelector('input[type="hidden"]');
	id_receptor = new URL(location.href).searchParams.get('id-receptor');
	nombre_receptor = new URL(location.href).searchParams.get('nombre-receptor');

	constructor() {
		super();
	}

	streamMensajesDirectos() {
		const evtSource = new EventSource(`${this.ENDPOINTS.STREAM_MENSAJES_DIRECTOS}?ultimo_id=${this.ultimoId}&id_receptor=${this.id_receptor}`);

		evtSource.addEventListener("mensaje", (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);
			this.MENSAJES_OUTPUT.insertAdjacentHTML("beforeend", content);
			this.formHandler();

			this.ultimoId = mensaje.id_mensaje;
		});

		evtSource.addEventListener("ping", async (event) => {
			const form = new FormData();
			form.append("id_receptor", this.id_receptor);

			await fetch(this.ENDPOINTS.ULTIMA_CONEXION_DIRECTA, {
				method: "POST",
				body: form
			});
		});
	}

	writeChat() {
		this.h1.innerHTML = `Chat privado con ${this.nombre_receptor}`;
		this.input.setAttribute('value', `${this.id_receptor}`);
	}

	async writeMensajeDirecto(form, method, action) {
		await this.fetchData(form, method, action);
	}
}