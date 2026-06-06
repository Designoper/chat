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

	async usuariosTemplate(fetchedUsuarios) {

		const usuarios = await Promise.all(
			fetchedUsuarios.map(async usuario => {
				const mensajesNoLeidos = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.NO_LEIDOS, 'get',
					{
						"id_receptor": usuario.id_usuario
					}
				);

				const num = mensajesNoLeidos.content.num_mensajes;

				const badge = num > 0
					? `<data value="${num}">${num}</data>`
					: '';

				const ultimoMensaje = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.ULTIMO_MENSAJE, 'get',
					{
						"id_receptor": usuario.id_usuario
					}
				);

				const fechaMensaje = ultimoMensaje?.content?.fecha_envio
					? `<date>${this.fullDate(ultimoMensaje.content.fecha_envio)}</date>`
					: '';

				const mensajePropio = ultimoMensaje?.content?.id_emisor === this.id_usuario
					? 'Tú'
					: `<span translate="no">${ultimoMensaje.content.nombre_usuario}</span>`;

				const lastMessage = ultimoMensaje?.content?.contenido
					? `<p>${mensajePropio}: ${ultimoMensaje.content.contenido}</p>`
					: '';

				const usuarios =
					`<li>
						<a href="chat.php?id_receptor=${usuario.id_usuario}&nombre_receptor=${usuario.nombre_usuario}">
							<h2 translate="no">${usuario.nombre_usuario}</h2>${badge}
							${fechaMensaje}
							${lastMessage}
						</a>
					</li>`;

				return usuarios;
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
		return response.content;
	}
}
