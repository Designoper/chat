import { Form } from "./Form.js";

export class Usuario extends Form {
	user = null;
	constructor() {
		super();
	}

	async initialize() {
		this.formHandler();
	}

	async createUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 201) {
			sessionStorage.setItem('id_usuario', response.content.id_usuario);
			location.href = 'chat.html';
		}
	}

	async loginUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 200) {
			location.href = 'chat.html';
		}
	}

	async sessionCheck() {
		const response = await this.simpleFetch(this.ENDPOINTS.CURRENT_USUARIOS, 'get');

		if (response.status === 409) {
			location.href = 'crear-usuario.html';
		}

		this.user = response.content.id_usuario;

		const usuarioInput = document.querySelector('input#id_usuario');
		if (usuarioInput) {
			usuarioInput.setAttribute('value', this.user);
		}
	}
}

(async () => {
	await new Usuario().initialize();
})();
