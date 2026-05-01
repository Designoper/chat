import Endpoint from "./Endpoint.js";

export default class Grupo extends Endpoint {
	menu = document.querySelector('menu');
	div = document.querySelector('div');

	constructor() {
		super();
	}

	async initialize() {
		this.getGruposMiembro();
		this.getGruposPendiente();
	}

	async getGruposMiembro() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_GRUPOS_MIEMBRO);
		this.printGruposMiembro(response);
	}

	async getGruposPendiente() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_GRUPOS_PENDIENTE);
		this.printGruposPendiente(response);
	}

	async printGruposMiembro(grupos) {
		const content = await this.gruposMiembroTemplate(grupos.content);
		this.menu.innerHTML = content;
		this.formHandler();
	}

	printGruposPendiente(grupos) {
		const content = this.gruposPendienteTemplate(grupos.content);
		this.div.innerHTML = content;
		this.formHandler();
	}

	async gruposMiembroTemplate(fetchedGrupos) {

		const grupos = await Promise.all(
			fetchedGrupos.map(async grupo => {

				const invitables = await this.simpleFetch(
					`${this.ENDPOINTS.GET_GRUPOS_NO_MIEMBRO}?id_grupo=${grupo.id_grupo}`
				);

				const opciones = invitables.content
					.map(user => `<option value="${user.id_usuario}">${user.nombre_usuario}</option>`)
					.join('');

				const formInvitar =
				`<li>
					<p>${grupo.nombre_grupo}</p>
					<form name="invitar">
						<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
						<select name="id_usuario">
							<option>Invitar a...</option>
							${opciones}
						</select>
						<button>Mandar invitación</button>
					</form>
					<a href="chat-grupal.php?id-grupo=${grupo.id_grupo}&nombre-grupo=${grupo.nombre_grupo}">Entrar</a>
				</li>`

				return formInvitar;

			})
		);

		return grupos.join('');
	}

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`<li>
				<p>${grupo.nombre_grupo}</p>
				<form name="aceptar-invitacion">
					<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
					<button>Aceptar invitación</button>
				</form>
			</li>`
		).join('');

		return grupos;
	}

	async createGrupo(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 201) {
			this.getGruposMiembro();
		}
	}

	async aceptarInvitacion(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 200) {
			this.getGruposMiembro();
			this.getGruposPendiente();
		}
	}

	async invitar(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 201) {
			this.getGruposMiembro();
			this.getGruposPendiente();
		}
	}
}

(async () => {
	await new Grupo().initialize();
})();
