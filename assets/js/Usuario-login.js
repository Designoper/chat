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
			form.onsubmit = (submitEvent) => {
				submitEvent.preventDefault();
				this.login(form);
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

	async login(form) {
		const response = await this.fetchData(form);
		if (response.status === 200) {
			sessionStorage.setItem('id_usuario', response.content.id_usuario);
			location.href = 'chat.html';
		}
	}
}

(async () => {
	await new Usuario().initialize();
})();
