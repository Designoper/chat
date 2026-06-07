import TemporalFormat from "./Temporal.js";

export default class Usuario extends TemporalFormat {
	id_usuario;

	constructor() {
		super();
	}

	async getUsuarios() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.OTROS, 'get');
		return response.content;
	}

	async printUsuarios(usuarios) {
		const content = await this.usuariosTemplate(usuarios.content);
		this.menu.insertAdjacentHTML('beforeend', content);
	}

	usuariosTemplate(fetchedUsuarios) {

		const usuarios = fetchedUsuarios.map(usuario => {
			const badge = usuario.num_mensajes > 0
				? `<data>${usuario.num_mensajes}</data>`
				: '';

			const autorMensaje = usuario.id_emisor === this.id_usuario
				? 'Tú'
				: `<span translate="no">${usuario.nombre_usuario}</span>`;

			const lastMessage = usuario.contenido !== null
				? `<date>${this.fullDate(usuario.fecha_envio)}</date>
					<p>${autorMensaje}: ${usuario.contenido}</p>`
				: '';

			const usuario2 =
				`<li>
					<a href="./chat.php?id_receptor=${usuario.id_usuario}&nombre_receptor=${usuario.nombre_usuario}">
						<h2 translate="no">${usuario.nombre_usuario}</h2>
						${badge}
						${lastMessage}
					</a>
				</li>`;

			return usuario2;
		});

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
		return response.content;
	}
}
