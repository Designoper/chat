import { Fetch } from "./Fetch.js";

export class Usuario extends Fetch {
	static ENDPOINT = `${location.protocol}//${location.host}/api/login`;

	constructor() {
		super();
	}

	async initialize() {
		this.formAction();
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
				form.action = Usuario.ENDPOINT;
			}
		});
	}

	async writeUsuario(form, method) {
		const response = await this.fetchData(form);
		if (response.status === 200 && method === 'POST') {
			sessionStorage.setItem('id_usuario', response.content.id_usuario);
			location.href = 'chat.html';
		}
	}
}

(async () => {
	await new Usuario().initialize();
})();
