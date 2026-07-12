import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	constructor() {
		super();
	}

	// MARK: CREATE GRUPO

	async createGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.CREAR);
		if (response.status === 201) {
			form.querySelector("output").innerHTML = 'Grupo creado con éxito.';
		}
	}

	async eliminarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ELIMINAR);

		if (response.status === 204) {
			globalThis.location.href = 'chats.php';
		}
	}

	async abandonarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ABANDONAR);

		if (response.status === 204) {
			globalThis.location.href = 'chats.php';
		}
	}
}