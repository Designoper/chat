import TemporalAPI from "./common/TemporalAPI.js";

export default class Usuario extends TemporalAPI {
	usuario = {
		ulid_usuario: {},
		nombre_usuario: {},
		codigo_contacto: {}
	};

	constructor() {
		super();
	}

	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			globalThis.location.href = 'chats.php';
		}
	}

	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			globalThis.location.href = 'chats.php';
		}
	}

	async logout(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGOUT);
		if (response.status === 204) {
			globalThis.location.href = 'index.php';
		}
	}

	async deleteUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (response.status === 204) {
			globalThis.location.href = 'registro.php';
		}
	}

	async cambiarNombre(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_NOMBRE);
	}

	async cambiarPassword(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_PASSWORD);
	}

	async currentUsuario() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = response.json;
	}

	imprimirCodigo() {
		const response = document.querySelector('header p');
		response.innerHTML = this.usuario.codigo_contacto;
	}
}
