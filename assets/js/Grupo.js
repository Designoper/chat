import { Endpoint } from "./Endpoint.js";

export class Grupo extends Endpoint {
	user = null;
	MAIN = document.getElementById('main');
	menu = document.querySelector('menu');
	// USUARIOS_OUTPUT = document.getElementById('usuariosoutput');

	constructor() {
		super();
	}

	async initialize() {
		this.formHandler();
		this.getGrupos();
	}

	async getGrupos() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_GRUPOS);
		this.printGrupos(response);
	}

	printGrupos(grupos) {
		const content = this.gruposTemplate(grupos.content);
		this.menu.innerHTML = content;
	}

	gruposTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`<li>
				<a href="chat-grupal.php?id=${grupo.id_grupo}">${grupo.nombre_grupo}</a>
			</li>`
		).join('');

		return grupos;
	}

	async createGrupo(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 201) {
			this.getGrupos();
		}
	}

	async deleteUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 204) {
			location.href = 'crear-usuario.php';
		}
	}

	async sessionCheck() {
		const response = await this.simpleFetch(this.ENDPOINTS.CURRENT_USUARIOS);

		this.user = response.content.id_usuario;
	}
}

(async () => {
	await new Grupo().initialize();
})();
