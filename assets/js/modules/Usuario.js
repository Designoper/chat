import TemporalAPI from "./universal/TemporalAPI.js";

export default class Usuario extends TemporalAPI {
	usuario = {};

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
		// if (response.status === 201) { }
	}

	async cambiarPassword(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_PASSWORD);
		// if (response.status === 201) { }
	}

	async solicitarContacto(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.SOLICITAR_CONTACTO);
		// if (response.status === 201) { }
		console.log(response);
	}

	async sessionCheck() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = response.json;
	}
}
