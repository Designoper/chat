import { Fetch } from "./Fetch.js";

export class Usuario extends Fetch {
	static ENDPOINT = `${location.protocol}//${location.host}/api/usuarios`;

	// static DOM_ELEMENTS = {
	// 	OUTPUT: document.getElementById('fetchoutput'),
	// 	ERROR_CONTAINER: document.getElementById('errorcontainer')
	// };

	constructor() {
		super();
	}

	async initialize() {
		this.formAction();
		this.formHandler();
		// await this.getMensajes();

		// setInterval(async () => {
		// 	await this.getMensajes();
		// }, 2000);
	}

	// async getMensajes() {
	// 	const response = await this.simpleFetch(Mensaje.ENDPOINT);
	// 	this.printMensajes(response);
	// }

	// static mensajesTemplate(fetchedMensajes) {

	// 	const mensajes = fetchedMensajes.map(mensaje =>
	// 		`<p>${mensaje.contenido} ${mensaje.fecha_creacion}</p>`
	// 	).join('');

	// 	return mensajes;
	// }

	printMensajes(mensajes) {

		if (mensajes.content.length === 0) {
			Usuario.DOM_ELEMENTS.OUTPUT.innerHTML = "";
			Usuario.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = mensajes.message;
		}

		else {
			const content = Usuario.mensajesTemplate(mensajes.content);
			Usuario.DOM_ELEMENTS.OUTPUT.innerHTML = content;
			Usuario.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = "";
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
						this.writeUsuario(form, method);
				}
			}
		});
	}

	formAction() {
		const forms = document.querySelectorAll('form');

		forms.forEach(form => {
			if (form.getAttribute('action') === null) {
				form.action = this.ENDPOINT;
			}
		});
	}

	async writeUsuario(form, method) {
		const response = await this.fetchData(form);
        if (response.status === 201 && method === 'POST') {
			sessionStorage.setItem('id_usuario', response.content.id_usuario);
            location.href = 'index.html';
        }
	}
}

(async () => {
	await new Usuario().initialize();
})();
