import { Fetch } from "./Fetch.js";

export class Mensaje extends Fetch {
	static ENDPOINT = `${location.protocol}//${location.host}/api/mensajes`;

	static DOM_ELEMENTS = {
		OUTPUT: document.getElementById('fetchoutput'),
		ERROR_CONTAINER: document.getElementById('errorcontainer')
	};

	constructor() {
		super();
	}

	async initialize() {
		this.sessionCheck();
		this.formAction();
		await this.getMensajes();

		setInterval(async () => {
			await this.getMensajes();
		}, 2000);
	}

	sessionCheck() {
		const usuario = sessionStorage.getItem('id_usuario');
		if (!usuario) {
			location.href = 'crear-usuario.html';
		}

		const usuarioInput = document.querySelector('input#id_usuario');
		if (usuarioInput) {
			usuarioInput.setAttribute('value', usuario);
		}
	}

	async getMensajes() {
		const response = await this.simpleFetch(Mensaje.ENDPOINT);
		this.printMensajes(response);
	}

	static mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`
			<article ${mensaje.id_usuario == sessionStorage.getItem('id_usuario') ? 'class="mensaje-propio"': ''}>
				<p>${mensaje.nombre}</p>
				<p>${mensaje.contenido}</p>
				<p>${mensaje.fecha_creacion}</p>
				${mensaje.id_usuario == sessionStorage.getItem('id_usuario')
					? `<form action="${Mensaje.ENDPOINT}/${mensaje.id_mensaje}" method="dialog">
						<button type="submit" value='POST'>Eliminar mensaje</button>
						</form>`
					: ''}
			</article>

			</dialog>
		`
		).join('');

		return mensajes;
	}

	printMensajes(mensajes) {

		if (mensajes.content.length === 0) {
			Mensaje.DOM_ELEMENTS.OUTPUT.innerHTML = "";
			Mensaje.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = mensajes.message;
		}

		else {
			const content = Mensaje.mensajesTemplate(mensajes.content);
			Mensaje.DOM_ELEMENTS.OUTPUT.innerHTML = content;
			Mensaje.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = "";
		}

		this.formHandler();
	}

	formHandler() {
		const forms = document.querySelectorAll('form');

		forms.forEach(form => {

			const submitButton = form.querySelector('button:not([type="reset"], [type="button"])');
			const method = submitButton.value;

			form.onsubmit = (submitEvent) => {
				submitEvent.preventDefault();
				switch (method) {
					case 'POST':
						this.writeMensaje(form, method);
				}
			}
		});
	}

	formAction() {
		const forms = document.querySelectorAll('form');

		forms.forEach(form => {
			if (form.getAttribute('action') === null) {
				form.action = Mensaje.ENDPOINT;
			}
		});
	}

	async writeMensaje(form) {
		await this.fetchData(form);
	}
}

(async () => {
	await new Mensaje().initialize();
})();
