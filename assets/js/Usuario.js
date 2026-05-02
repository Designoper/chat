import Endpoint from "./Endpoint.js";

export default class Usuario extends Endpoint {
	id_usuario = null;
	MAIN = document.getElementById('main');
	menu = document.querySelector('menu');
	USUARIOS_OUTPUT = document.getElementById('usuariosoutput');

	constructor() {
		super();
	}

	async initialize() {
		this.formHandler();
	}

	async getUsuarios() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_USUARIOS);
		this.printUsuarios(response);
	}

	printUsuarios(usuarios) {
		const content = this.usuariosTemplate(usuarios.content);
		this.menu.insertAdjacentHTML('beforeend', content);
	}

	usuariosTemplate(fetchedUsuarios) {

		const usuarios = fetchedUsuarios.map(usuario =>
			`<li>
				<a href="chat-privado.php?id-receptor=${usuario.id_usuario}&nombre-receptor=${usuario.nombre_usuario}">${usuario.nombre_usuario}</a>
			</li>`
		).join('');

		return usuarios;
	}

	async createUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 201) {
			location.href = 'sala-principal.php';
		}
	}

	async deleteUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 204) {
			location.href = 'crear-usuario.php';
		}
	}

	async loginUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
		console.log(response)
		if (response.status === 200) {
			location.href = 'sala-principal.php';
		}
	}

	async logout(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 204) {
			location.href = 'index.html';
		}
	}

	async sessionCheck() {
		const response = await this.simpleFetch(this.ENDPOINTS.CURRENT_USUARIOS);

		this.id_usuario = response.content.id_usuario;
	}
}

(async () => {

	await new Usuario().initialize();

	if (location.pathname === '/sala-principal.php') {
		await new Usuario().getUsuarios();
	}

})();
