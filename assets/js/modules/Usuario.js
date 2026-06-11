import TemporalAPI from "./universal/TemporalAPI.js";

export default class Usuario extends TemporalAPI {
	usuario = {};

	constructor() {
		super();
	}

	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			location.href = 'sala-principal.php';
		}
	}

	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			location.href = 'sala-principal.php';
		}
	}

	async logout(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGOUT);
		if (response.status === 204) {
			location.href = 'index.php';
		}
	}

	async deleteUsuario(form) {
		const { status, json } = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (status === 204) {
			location.href = 'crear-usuario.php';
		}
	}

	async sessionCheck() {
		const { json } = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = json;
	}
}
