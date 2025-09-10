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
		this.formAction();
		await this.getMensajes();

		setInterval(async () => {
			await this.getMensajes();
		}, 2000);
	}

	async getMensajes() {
		const response = await this.simpleFetch(Mensaje.ENDPOINT);
		this.printMensajes(response);
	}

	static mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`<p>${mensaje.contenido} ${mensaje.fecha_creacion}</p>`
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
					// case 'GET':
					//     this.filterLibros(form);
					//     break;
					case 'POST':
						// case 'PUT':
						// case 'DELETE':
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
		// if (response.status === 201 && method === 'POST' || response.status === 200 && method === 'PUT' || response.status === 204 && method === 'DELETE') {
		// 	await this.getLibros();
		// }
	}
}

(async () => {
	await new Mensaje().initialize();
})();
