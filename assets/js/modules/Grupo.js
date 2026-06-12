import Contacto from "./Contacto.js";
import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	output = document.querySelector('output');
	invitacionesMenu = this.output.querySelector('menu:nth-of-type(1)');

	constructor() {
		super();
	}

	// async getGruposMiembro() {
	// 	const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.MIEMBRO, 'get');
	// 	return response;
	// }

	// async printGruposMiembro(grupos) {
	// 	const content = await this.gruposMiembroTemplate(grupos);
	// 	return response;
	// }

	// async getGruposPendiente() {
	// 	const { json } = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.PENDIENTE, 'get');
	// 	return json;
	// }

	// async printGruposPendiente() {
	// 	const response = await this.getGruposPendiente();
	// 	const content = this.gruposPendienteTemplate(response);
	// 	this.invitacionesMenu.innerHTML = content;
	// }

	gruposNoMiembroTemplate(fetchedGrupos) {

		const opciones = fetchedGrupos
			.map(grupo => `<option translate="no" value="${grupo.id_usuario}">${grupo.nombre_usuario}</option>`)
			.join('');

		return `<option value="">Añadir a...</option>${opciones}`;
	}


	// async gruposMiembroTemplate(fetchedGrupos) {

	// 	const grupos = await Promise.all(
	// 		fetchedGrupos.map(async grupo => {

	// 			const invitables = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.NO_MIEMBRO, 'get',
	// 				{
	// 					"id_grupo": grupo.id_grupo
	// 				}
	// 			);

	// 			const opciones = invitables.content
	// 				.map(user => `<option translate="no" value="${user.id_usuario}">${user.nombre_usuario}</option>`)
	// 				.join('');

	// 			const formInvitar =
	// 				`<li>

	// 					<a href="./chat.php?id_grupo=${grupo.id_grupo}&nombre_grupo=${grupo.nombre_grupo}">
	// 						<h2 translate="no">${grupo.nombre_grupo}</h2>
	// 						${badge}
	// 						${lastMessage}
	// 					</a>

	// 					<form method="POST" name="invitar">
	// 						<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
	// 						<select name="id_invitado" id="id_invitado" required>
	// 							<option value="">Añadir a...</option>
	// 							${opciones}
	// 						</select>
	// 						<button>Invitar</button>
	// 					</form>

	// 				</li>`;

	// 			return formInvitar;
	// 		})
	// 	);

	// 	return grupos.join('');
	// }

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo => {

			const template =
				`
					<li>
						<h2 translate="no">${grupo.nombre_grupo}</h2>

						<form method="POST" name="aceptarInvitacion">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<button>Aceptar</button>
						</form>

						<form method="POST" name="rechazarInvitacion">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<button>Rechazar</button>
						</form>
					</li>
				`;

			return template;
		});

		return grupos.join('');
	}

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