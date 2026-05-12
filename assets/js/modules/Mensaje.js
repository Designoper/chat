import Usuario from "./Usuario.js";
import formatearFecha from "../utils/fecha.js";

export default class Mensaje extends Usuario {
	MENSAJES_OUTPUT = document.querySelector('output');
	obj = {};
	urlStream = new URL(this.ENDPOINTS.STREAM_MENSAJES);
	url = new URL(location.href);

	h1 = document.querySelector('h1');
	input = document.querySelector('input[type="hidden"]');

	id_receptor = this.url.searchParams.get('id-receptor');
	nombre_receptor = this.url.searchParams.get('nombre-receptor');

	id_grupo = this.url.searchParams.get('id-grupo');
	nombre_grupo = this.url.searchParams.get('nombre-grupo');

	constructor() {
		super();
	}

	async getMensajes(endpointMensaje) {
		const response = await this.simpleFetch(endpointMensaje);
		const mensajes = this.mensajesTemplate(response.content);
		this.MENSAJES_OUTPUT.innerHTML = mensajes;

		let id;

		response.content.length > 0
			? id = response.content[response.content.length - 1].id_mensaje
			: id = "";

		return id;
	}

	setData(obj) {
		const params = new URLSearchParams();

		for (const [key, value] of Object.entries(obj)) {
			this.obj[key] = value;
			params.append(key, value);
		}

		this.urlStream.search = params;
	}

	streamMensajes(endpointUltimoId) {

		const evtSource = new EventSource(this.urlStream);

		evtSource.addEventListener("mensaje", (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);

			this.MENSAJES_OUTPUT.insertAdjacentHTML("beforeend", content);
			this.formHandler();
		});

		evtSource.addEventListener("new mensaje", async (event) => {
			const id = JSON.parse(event.data);

			this.obj.ultimo_id = id;

			const response = await this.fetchPostNoForm(endpointUltimoId, this.obj);
		});
	}

	mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`
			<article ${mensaje.id_emisor == this.id_usuario ? 'class="mensaje-propio"' : ''}>
				<p>${mensaje.nombre_usuario}</p>
				<p>${mensaje.contenido}</p>
				<p>${formatearFecha(mensaje.fecha_envio).toLocaleString(undefined,
				{
					weekday: "long",
					year: "numeric",
					month: "numeric",
					day: "numeric",
					hour: "numeric",
					minute: "numeric"
				}
			)}</p>
				${mensaje.id_emisor == this.id_usuario
				? `<form name="eliminar-mensaje" action="${this.ENDPOINTS.ELIMINAR_MENSAJES}/${mensaje.id_mensaje}">
						<button>
							<svg viewBox="0 0 928 983">
								<path d="M880.09 95.543H681.62l-3.2-43.688C676.31 23.079 652.35.81 623.5.81H303.82c-28.85 0-52.81 22.27-54.92 51.045l-3.2 43.688H47.23c-26.06 0-47.17 21.12-47.17 47.172s21.11 47.172 47.17 47.172h832.86c26.06 0 47.18-21.12 47.18-47.172s-21.12-47.172-47.18-47.172zM54.64 225.899l49.25 672.171c3.51 47.98 43.47 85.12 91.58 85.12h536.38c48.12 0 88.07-37.14 91.58-85.12l49.25-672.171H54.64zm241.1 601.221c-.44.02-.87.04-1.3.04a20.31 20.31 0 0 1-20.24-19.01l-26.83-421.639c-.71-11.182 7.78-20.831 18.97-21.544 11.17-.705 20.82 7.784 21.54 18.966l26.83 421.637c.7 11.19-7.79 20.83-18.97 21.55zm188.21-20.25c0 11.2-9.09 20.29-20.29 20.29s-20.29-9.09-20.29-20.29V385.218c0-11.207 9.09-20.284 20.29-20.284s20.29 9.077 20.29 20.284V806.87zm196-420.359L653.11 808.15c-.68 10.74-9.6 19.01-20.22 19.01-.44 0-.87-.02-1.31-.04a20.31 20.31 0 0 1-18.96-21.55l26.83-421.637c.71-11.182 10.37-19.67 21.54-18.966 11.18.713 19.67 10.362 18.96 21.544z"/>
							</svg>
						</button>
					</form>`
				: ''
			}
			</article>
			`
		).join('');

		return mensajes;
	}

	async writeMensaje(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async writeMensajeDirecto(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async writeMensajeGrupal(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async deleteMensaje(form, method) {
		const response = await this.fetchData(form, method);
		if (response.status === 204) {
			form.closest("article").remove();
		}
	}

	writeChat(titulo, id) {
		this.h1.innerHTML = titulo;
		this.input.setAttribute('value', `${id}`);
	}
}