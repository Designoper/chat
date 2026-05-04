import Endpoint from "./Endpoint.js";

export default class Usuario extends Endpoint {
	id_usuario;
	menu = document.querySelector('menu');

	constructor() {
		super();
		this.sessionCheck();
	}

	async initialize() {
		this.formHandler();
	}

	async getUsuarios() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_USUARIOS);
		this.printUsuarios(response);
	}

	async printUsuarios(usuarios) {
		const content = await this.usuariosTemplate(usuarios.content);
		this.menu.insertAdjacentHTML('beforeend', content);
	}

	async usuariosTemplate(fetchedUsuarios) {

		const usuarios = await Promise.all(
			fetchedUsuarios.map(async usuario => {
				const mensajesNoLeidos = await this.simpleFetch(
					`${this.ENDPOINTS.GET_MENSAJES_DIRECTOS_NO_LEIDOS}?id_receptor=${usuario.id_usuario}`
				);

				const test = mensajesNoLeidos.content.num_mensajes

				const test2 = test > 0
					? `(${test})`
					: '';

				const dom =
				`<li>
					<a href="chat-privado.php?id-receptor=${usuario.id_usuario}&nombre-receptor=${usuario.nombre_usuario}">${usuario.nombre_usuario}</a>
					<span>${test2}</span>
				</li>`;

				return dom;
			})
		);

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
			location.href = 'crear-usuario.html';
		}
	}

	async loginUsuario(form, method, action) {
		const response = await this.fetchData(form, method, action);
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

	async getMensajesNoLeidos() {
		const response = await this.simpleFetch(this.ENDPOINTS.GET_MENSAJES_NO_LEIDOS);
		const span = document.querySelector('menu li:first-child span');

		if (response.content.num_mensajes > 0) {
			span.textContent = `(${response.content.num_mensajes})`;
		}
	}
}

const usuario = new Usuario();
usuario.initialize();

if (location.pathname === '/sala-principal.php') {
	usuario.getUsuarios();
	usuario.getMensajesNoLeidos();
}
