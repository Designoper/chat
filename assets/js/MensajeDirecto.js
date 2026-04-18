import { Usuario } from "./Usuario.js";

export class MensajeDirecto extends Usuario {
	// MENSAJES_OUTPUT = document.getElementById('fetchoutput');

	constructor() {
		super();
	}

	async initialize() {
		this.sessionCheck();
		this.getUsuarios();
		// await this.getMensajesDirectos();
	}

	writeChat() {
		this.MAIN.innerHTML = `
			<h1>Chat directo con ${this.id_receptor}</h1>
			<section id="fetchoutput"></section>

			<form name="crear-mensaje-directo" action="${this.ENDPOINTS.CREAR_MENSAJES_DIRECTOS}">
				<input type="hidden" name="id_receptor" value="${this.id_receptor}">
				<input placeholder="Mensaje" name="contenido" autocomplete="off" minlength="1" maxlength="255" required>
				<button>
					<svg viewBox="0 0 512 512">
						<path
							d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
					</svg>
				</button>
			</form>

			<a href="sala-principal.html">Volver a sala principal</a>
			<a href="sala-chat-directo.html">Chatear con otro usuario</a>
		`;
		setInterval(async () => {
			await this.getMensajesDirectos(this.id_receptor);
		}, 2000);
	}

	async getMensajesDirectos(receptor) {
		const response = await this.simpleFetch(`${this.ENDPOINTS.GET_MENSAJES_DIRECTOS}?id_receptor=${receptor}`);
		this.printMensajesDirectos(response);
	}

	mensajesDirectosTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`
			<article ${mensaje.id_usuario == this.user ? 'class="mensaje-propio"' : ''}>
				<p>${mensaje.nombre_emisor}</p>
				<p>${mensaje.contenido}</p>
				<p>${mensaje.fecha_creacion}</p>
				${mensaje.id_usuario == this.user
				? `<form name="eliminar-mensaje" action="${this.ENDPOINTS.ELIMINAR_MENSAJES}/${mensaje.id_mensaje}">
						<button>
							<svg viewBox="0 0 928 983">
								<path d="M880.09 95.543H681.62l-3.2-43.688C676.31 23.079 652.35.81 623.5.81H303.82c-28.85 0-52.81 22.27-54.92 51.045l-3.2 43.688H47.23c-26.06 0-47.17 21.12-47.17 47.172s21.11 47.172 47.17 47.172h832.86c26.06 0 47.18-21.12 47.18-47.172s-21.12-47.172-47.18-47.172zM54.64 225.899l49.25 672.171c3.51 47.98 43.47 85.12 91.58 85.12h536.38c48.12 0 88.07-37.14 91.58-85.12l49.25-672.171H54.64zm241.1 601.221c-.44.02-.87.04-1.3.04a20.31 20.31 0 0 1-20.24-19.01l-26.83-421.639c-.71-11.182 7.78-20.831 18.97-21.544 11.17-.705 20.82 7.784 21.54 18.966l26.83 421.637c.7 11.19-7.79 20.83-18.97 21.55zm188.21-20.25c0 11.2-9.09 20.29-20.29 20.29s-20.29-9.09-20.29-20.29V385.218c0-11.207 9.09-20.284 20.29-20.284s20.29 9.077 20.29 20.284V806.87zm196-420.359L653.11 808.15c-.68 10.74-9.6 19.01-20.22 19.01-.44 0-.87-.02-1.31-.04a20.31 20.31 0 0 1-18.96-21.55l26.83-421.637c.71-11.182 10.37-19.67 21.54-18.966 11.18.713 19.67 10.362 18.96 21.544z"/>
							</svg>
						</button>
					</form>`
				: ''}
			</article>
			`
		).join('');

		return mensajes;
	}

	printMensajesDirectos(mensajes) {
		const output = document.getElementById('fetchoutput');
		const content = this.mensajesDirectosTemplate(mensajes.content);
		output.innerHTML = content;

		this.formHandler();
	}

	async writeMensajeDirecto(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async usuarioReceptor(form, method, action) {
		const response = await this.fetchData(form, method, action);
		console.log(response);
		if (response.status === 200) {
			this.writeChat();
			this.formHandler();
		}
	}
}

// async deleteMensaje(form, method) {
// 	await this.fetchData(form, method);
// }

(async () => {
	await new MensajeDirecto().initialize();
})();
