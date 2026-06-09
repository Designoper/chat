import TemporalFormat from "./Temporal.js";

export default class Usuario extends TemporalFormat {
	id_usuario;
	output = document.querySelector('output>menu');

	constructor() {
		super();
	}

	async getUsuarios() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CONTACTOS, 'get');
		return response.content;
	}

	contactosTemplate(fetchedContactos) {

		const usuarios = fetchedContactos.map(contacto => {

			let id;

			if (contacto.tipo === 'usuario') {
				id = 'id_receptor';
			}

			if (contacto.tipo === 'grupo') {
				id = 'id_grupo';
			}

			const badge = contacto.num_mensajes > 0
				? `<data>${contacto.num_mensajes}</data>`
				: '';

			const autorMensaje = contacto.id_emisor === this.id_usuario
				? 'Tú'
				: `<span translate="no">${contacto.nombre}</span>`;

			const lastMessage = contacto.contenido !== null
				? `<date>${this.fullDate(contacto.fecha_envio)}</date>
					<p>${autorMensaje}: ${contacto.contenido}</p>`
				: '';

			const template =
				`<li>
					<a href="./chat.php?${id}=${contacto.id}&nombre=${contacto.nombre}">
						<h2 translate="no">${contacto.nombre}</h2>
						${badge}
						${lastMessage}
					</a>
				</li>`;

			return template;
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

	streamContactos() {

		const evtSource = new EventSource(this.ENDPOINTS.GET.USUARIOS.STREAM);

		evtSource.addEventListener("new update", (event) => {
			const contactos = JSON.parse(event.data);
			const content = this.contactosTemplate(contactos);

			this.output.setHTML(`${content}`, {
				sanitizer: new Sanitizer({
					comments: false,
				})
			});
		});
	}
}
