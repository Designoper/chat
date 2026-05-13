import Endpoint from "./Endpoint.js";

export default class Usuario extends Endpoint {
	id_usuario;
	menu = document.querySelector('menu');

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

				const num = mensajesNoLeidos.content.num_mensajes;

				const badge = num > 0
					? `(${num})`
					: '';

				return `
                <li>
                    <a href="chat-privado.php?id-receptor=${usuario.id_usuario}&nombre-receptor=${usuario.nombre_usuario}">
                        ${usuario.nombre_usuario}
                    </a>
                    <span>${badge}</span>
                </li>
            `;
			})
		);

		return usuarios.join('');
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
		if (response.status === 200) {
			location.href = 'sala-principal.php';
		}
	}

	async logout(form, method, action) {
		const response = await this.fetchData(form, method, action);
		if (response.status === 204) {
			location.href = 'index.php';
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
