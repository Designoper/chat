import TemporalFormat from "./universal/Temporal.js";

export default class Usuario extends TemporalFormat {
	usuario = {
		id_usuario: undefined,
		nombre_usuario: undefined
	};

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
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (response.status === 204) {
			location.href = 'crear-usuario.php';
		}
	}

	async sessionCheck() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		console.log(response);
		this.usuario = response;
	}

	// async getMensajesNoLeidos() {
	// 	const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.NO_LEIDOS, 'get');
	// 	return response.content;
	// }
}
