import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	// output = document.querySelector('output');
	invitacionesMenu = this.output.querySelector('menu:nth-of-type(1)');

	constructor() {
		super();
	}

	gruposNoMiembroTemplate(fetchedGrupos) {

		const noMiembros = fetchedGrupos
			.map(grupo => `<option translate="no" value="${grupo.id_usuario}">${grupo.nombre_usuario}</option>`)
			.join('');

		const template =
			`
			<option value="">Añadir a...</option>
			${noMiembros}
			`;

		return template;
	}



	// MARK: GRUPOS CRUD

	async createGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.CREAR);
	}

	async eliminarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ELIMINAR);

		if (response.status === 204) {
			location.href = 'sala-principal.php';
		}
	}






	async abandonarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ABANDONAR);

		if (response.status === 204) {
			location.href = 'sala-principal.php';
		}
	}




	// MARK: STREAM NO MIEMBROS

	streamNoMiembros(idGrupo) {

		const url = `${this.ENDPOINTS.GET.GRUPOS.STREAM_NO_MIEMBRO}?id_grupo=${idGrupo}`;
		const evtSource = new EventSource(url);

		evtSource.addEventListener("no miembro", (event) => {
			const state = JSON.parse(event.data);

			const content = this.gruposNoMiembroTemplate(state);
			const select = document.getElementById('id_contacto');
			select.innerHTML = this.sanitize(content);
		});
	}
}