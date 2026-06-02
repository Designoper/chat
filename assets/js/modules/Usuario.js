import Endpoint from "./Endpoint.js";

export default class Usuario extends Endpoint {
	id_usuario;
	menu = document.querySelector('output>menu>li>menu');

	constructor() {
		super();
	}

	async getUsuarios() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.OTROS, 'get');
		this.printUsuarios(response);
	}

	async printUsuarios(usuarios) {
		const content = await this.usuariosTemplate(usuarios.content);
		this.menu.insertAdjacentHTML('beforeend', content);
	}

	async usuariosTemplate(fetchedUsuarios) {

		const usuarios = await Promise.all(
			fetchedUsuarios.map(async usuario => {
				const mensajesNoLeidos = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.NO_LEIDOS, 'get',
					{
						"id_receptor": usuario.id_usuario
					}
				);

				const ultimoMensaje = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.ULTIMO_MENSAJE, 'get',
					{
						"id_receptor": usuario.id_usuario
					}
				);

				const lastMessage = ultimoMensaje?.content?.contenido
					? `<p class="ultimo-mensaje">${ultimoMensaje.content.nombre_usuario}: ${ultimoMensaje.content.contenido}</p>`
					: '';

				const num = mensajesNoLeidos.content.num_mensajes;

				const badge = num > 0
					? ` (${num})`
					: '';

				const total =
					`<li>
						<p translate="no">${usuario.nombre_usuario}${badge}</p>
						<a href="chat.php?id_receptor=${usuario.id_usuario}&nombre_receptor=${usuario.nombre_usuario}">
							<svg viewBox="0 0 2481 2481">
								<path d="M573.027 1811.925h-407.68c-90.945 0-165.355-85.823-165.355-190.725V190.712C-.008 85.824 74.402-.013 165.347-.013h2149.601c90.953 0 165.357 85.837 165.357 190.725V1621.2c0 104.901-74.403 190.725-165.357 190.725H1310.686l-709.001 649.723c-23.693 21.712-58.535 25.996-86.787 10.696-28.251-15.313-43.669-46.856-38.414-78.56l96.543-581.859zm180.208-905.916c0-115.278-93.509-208.712-208.706-208.712-115.212 0-208.714 93.433-208.714 208.712s93.501 208.712 208.714 208.712c115.198 0 208.706-93.433 208.706-208.712zm695.688 0c0-115.278-93.494-208.712-208.706-208.712s-208.706 93.433-208.706 208.712 93.494 208.712 208.706 208.712 208.706-93.433 208.706-208.712zm695.688 0c0-115.278-93.494-208.712-208.706-208.712-115.198 0-208.706 93.433-208.706 208.712s93.509 208.712 208.706 208.712c115.212 0 208.706-93.433 208.706-208.712z"/>
							</svg>
						</a>
						<a href="videollamada.php?id-receptor=${usuario.id_usuario}&nombre-receptor=${usuario.nombre_usuario}">
							<img src="./assets/img/videollamada.svg">
						</a>
						${lastMessage}
					</li>`;

				return total;
			})
		);

		return usuarios.join('');
	}

	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			location.href = 'sala-principal.php';
		}
	}

	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			location.href = 'sala-principal.php';
		}
	}

	async logout(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGOUT);
		if (response.status === 204) {
			location.href = 'index.php';
		}
	}

	async deleteUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (response.status === 204) {
			location.href = 'crear-usuario.php';
		}
	}

	async sessionCheck() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.id_usuario = response.content.id_usuario;
	}

	async getMensajesNoLeidos() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.NO_LEIDOS, 'get');
		const span = document.querySelector('menu li:first-child span');

		if (response.content.num_mensajes > 0) {
			span.textContent = `(${response.content.num_mensajes})`;
		}
	}
}
