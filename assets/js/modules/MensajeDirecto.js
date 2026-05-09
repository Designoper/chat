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

	async getMensajesDirectos() {
		const response = await this.simpleFetch(`${this.ENDPOINTS.GET_MENSAJES_DIRECTOS}?id_receptor=${this.id_receptor}`);
		const mensajes = this.mensajesTemplate(response.content);
		this.MENSAJES_OUTPUT.innerHTML = mensajes;
		this.ultimoId = response.content[response.content.length - 1]?.id_mensaje;

		if (this.ultimoId === undefined) {
			this.ultimoId = "";
		}

		const form = new FormData();
		form.append("ultimo_id", this.ultimoId);
		form.append("id_receptor", this.id_receptor);

		await fetch(this.ENDPOINTS.ULTIMO_ID_DIRECTO, {
			method: "POST",
			body: form
		});
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

		evtSource.addEventListener("new mensaje", async (event) => {
			const id = JSON.parse(event.data);
			this.ultimoId = id;
			const form = new FormData();
			form.append("ultimo_id", id);
			form.append("id_receptor", this.id_receptor);

			await fetch(this.ENDPOINTS.ULTIMO_ID_DIRECTO, {
				method: "POST",
				body: form
			});

			// Auto-scroll
			// this.MENSAJES_OUTPUT.scrollTop = this.MENSAJES_OUTPUT.scrollHeight;
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