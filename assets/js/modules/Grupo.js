import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	constructor() {
		super();
	}

	// MARK: GRUPOS CRUD

	async createGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.CREAR);
	}

	async eliminarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ELIMINAR);

		if (response.status === 204) {
			globalThis.location.href = 'sala-principal.php';
		}
	}

	async abandonarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ABANDONAR);

		if (response.status === 204) {
			globalThis.location.href = 'sala-principal.php';
		}
	}
}