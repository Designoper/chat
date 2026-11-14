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

	// ============================================================================
	// MARK: CREATE USUARIO
	// ============================================================================
	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			globalThis.location.href = 'chats.php';
		}
	}

	// ============================================================================
	// MARK: LOGIN
	// ============================================================================
	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			globalThis.location.href = 'chats.php';
		}
	}

	// ============================================================================
	// MARK: LOGOUT
	// ============================================================================
	async logout(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGOUT);
		if (response.status === 204) {
			globalThis.location.href = 'index.php';
		}
	}

	// ============================================================================
	// MARK: DELETE USUARIO
	// ============================================================================
	async deleteUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (response.status === 204) {
			globalThis.location.href = 'registro.php';
		}
	}

	// ============================================================================
	// MARK: CAMBIAR NOMBRE
	// ============================================================================
	async cambiarNombre(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_NOMBRE);
		if (response.status === 200) {
			form.querySelector('output').textContent = `Nombre cambiado correctamente.`;
		}
	}

	// ============================================================================
	// MARK: CAMBIAR PASSWORD
	// ============================================================================

	async cambiarPassword(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_PASSWORD);
		if (response.status === 200) {
			form.querySelector('output').textContent = `Contraseña cambiada correctamente.`;
		}
	}

	// ============================================================================
	// MARK: CURRENT USUARIO
	// ============================================================================
	async currentUsuario() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = response.json;
	}

	// ============================================================================
	// MARK: IMPRIMIR CODIGO
	// ============================================================================
	imprimirCodigo() {
		const response = document.querySelector('header p');
		response.textContent = this.usuario.codigo_contacto;
	}
}
