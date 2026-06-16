import Contacto from "./Contacto.js";
import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	output = document.querySelector('output');
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

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo => {

			const template =
				`
					<li>
						<h2 translate="no">${grupo.nombre_grupo}</h2>

						<form method="POST" name="aceptarInvitacion">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<button>
								<img src="./assets/img/accept.svg">
							</button>
						</form>

						<form method="POST" name="rechazarInvitacion">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<button>
								<img src="./assets/img/rechazar.svg">
							</button>
						</form>
					</li>
				`;

			return template;
		});

		return grupos.join('');
	}

	// MARK: GRUPOS CRUD

	async createGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.CREAR);
	}

	async invitar(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.INVITAR);
	}

	async aceptarInvitacion(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ACEPTAR);
	}

	async rechazarInvitacion(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.RECHAZAR);
	}

	async abandonarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ABANDONAR);

		if (response.status === 204) {
			location.href = 'sala-principal.php';
		}
	}

	async eliminarGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ELIMINAR);

		if (response.status === 204) {
			location.href = 'sala-principal.php';
		}
	}

	// MARK: STREAM GRUPOS PENDIENTES

	streamGruposPendientes() {

		const evtSource = new EventSource(this.ENDPOINTS.GET.GRUPOS.STREAM);

		evtSource.addEventListener("grupo", (event) => {
			const state = JSON.parse(event.data);

			const content = this.gruposPendienteTemplate(state);
			this.invitacionesMenu.innerHTML = content;
		});
	}

	// MARK: STREAM NO MIEMBROS

	streamNoMiembros(idGrupo) {

		const url = `${this.ENDPOINTS.GET.GRUPOS.STREAM_NO_MIEMBRO}?id_grupo=${idGrupo}`;
		const evtSource = new EventSource(url);

		evtSource.addEventListener("no miembro", (event) => {
			const state = JSON.parse(event.data);

			const content = this.gruposNoMiembroTemplate(state);
			const select = document.getElementById('id_invitado');
			select.innerHTML = content;
		});
	}
}