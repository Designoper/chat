import { Usuario } from "./Usuario.js";

export class Mensaje extends Usuario {
	DOM_ELEMENTS = {
		OUTPUT: document.getElementById('fetchoutput'),
	};

	constructor() {
		super();
	}

	async initialize() {
		this.sessionCheck();
		await this.getMensajes();

		setInterval(async () => {
			await this.getMensajes();
		}, 2000);
	}

	async getMensajes() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_MENSAJES);
		this.printMensajes(response);
	}

	mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`
			<article ${mensaje.id_usuario == sessionStorage.getItem('id_usuario') ? 'class="mensaje-propio"' : ''}>
				<p>${mensaje.nombre}</p>
				<p>${mensaje.contenido}</p>
				<p>${mensaje.fecha_creacion}</p>
				${mensaje.id_usuario == sessionStorage.getItem('id_usuario')
				? `<form name="eliminar-mensaje" action="${this.ENDPOINTS.ELIMINAR_MENSAJES}/${mensaje.id_mensaje}">
						<button>
							<img src="../assets/img/papelera.svg" alt="Eliminar mensaje">
						</button>
					</form>`
				: ''}
			</article>
			`
		).join('');

		return mensajes;
	}

	printMensajes(mensajes) {

		const content = this.mensajesTemplate(mensajes.content);
		this.DOM_ELEMENTS.OUTPUT.innerHTML = content;

		this.formHandler();
	}

	async writeMensaje(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async deleteMensaje(form, method) {
		await this.fetchData(form, method);
	}
}

(async () => {
	await new Mensaje().initialize();
})();
