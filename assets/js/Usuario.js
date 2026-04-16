import { Endpoint } from "./Endpoint.js";

export class Usuario extends Endpoint {
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
			window.location.href = 'chat.html';
		}
	}

	async deleteUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 204) {
			window.location.href = 'crear-usuario.html';
		}
	}

	async loginUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 200) {
			window.location.href = 'chat.html';
		}
	}

	async logout(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 200) {
			window.location.href = 'index.html';
		}
	}

	async sessionCheck() {
		const response = await this.simpleFetch(this.ENDPOINTS.CURRENT_USUARIOS, 'get');

		if (response.status === 401) {
			window.location.href = 'crear-usuario.html';
		}

		this.user = response.content.id_usuario;
	}
}

(async () => {
	await new Usuario().initialize();
})();
