import Endpoint from "./Endpoint.js";

export default class Grupo extends Endpoint {
	outputMiembro = document.querySelector('output');
	outputPendiente = document.querySelector('section:nth-of-type(2) output');

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
		this.outputMiembro.innerHTML = content;
		this.formHandler();
	}

	printGruposPendiente(grupos) {
		const content = this.gruposPendienteTemplate(grupos.content);
		this.outputPendiente.innerHTML = content;
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
				`<article>

					<h3>${grupo.nombre_grupo}</h3>

					<form name="invitar">
						<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
						<select name="id_usuario" required>
							<option value="">Invitar a...</option>
							${opciones}
						</select>
						<button>Mandar invitación</button>
					</form>

					<a href="./chat-grupal.php?id-grupo=${grupo.id_grupo}&nombre-grupo=${grupo.nombre_grupo}">Entrar</a>

				</article>`

				return formInvitar;

			})
		);

		return grupos.join('');
	}

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`<article>
				<h3>${grupo.nombre_grupo}</h3>
				<form name="aceptar-invitacion">
					<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
					<button>Aceptar invitación</button>
				</form>
			</article>`
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
