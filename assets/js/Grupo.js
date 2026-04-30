import { Endpoint } from "./Endpoint.js";

export class Grupo extends Endpoint {
	// user = null;
	main = document.getElementById('main');
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

	printGruposMiembro(grupos) {
		const content = this.gruposMiembroTemplate(grupos.content);
		this.menu.innerHTML = content;
		this.formHandler();
	}

	printGruposPendiente(grupos) {
		const content = this.gruposPendienteTemplate(grupos.content);
		this.div.innerHTML = content;
		this.formHandler();
	}

	gruposMiembroTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`<li>
				<p>${grupo.nombre_grupo}</p>
				<form name="invitar">
					<p>Invitar a...</p>
					<button>Mandar inv</button>
				</form>
				<a href="chat-grupal.php?id=${grupo.id_grupo}">Entrar</a>
			</li>`
		).join('');

		return grupos;
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

	// async sessionCheck() {
	// 	const response = await this.simpleFetch(this.ENDPOINTS.CURRENT_USUARIOS);

	// 	this.user = response.content.id_usuario;
	// }
}

(async () => {
	await new Grupo().initialize();
})();
