import { Form } from "./Form.js";

export class Usuario extends Form {
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
			sessionStorage.setItem('id_usuario', response.content.id_usuario);
			location.href = 'chat.html';
		}
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
}

(async () => {
	await new Usuario().initialize();
})();
