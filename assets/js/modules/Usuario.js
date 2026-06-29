import TemporalAPI from "./universal/TemporalAPI.js";

export default class Usuario extends TemporalAPI {
	usuario = {};
	output = document.querySelector('output');
	invitacionDirectaMenu = this.output.querySelector('menu:nth-of-type(2)');

	constructor() {
		super();
	}

	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			globalThis.location.href = 'sala-principal.php';
		}
	}

	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			globalThis.location.href = 'sala-principal.php';
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

	async sessionCheck() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = response.json;
	}

	imprimirCodigo() {
		const response = document.querySelector('header p');
		response.innerHTML = this.usuario.codigo_contacto;
	}
}
