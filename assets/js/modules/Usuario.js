import TemporalAPI from "./universal/TemporalAPI.js";

export default class Usuario extends TemporalAPI {
	usuario = {};
	output = document.querySelector('output');
	contactosMenu = this.output.querySelector('menu:nth-of-type(2)');

	constructor() {
		super();
	}

	// MARK: CONTACTOS TEMPLATE

	usuariosTemplate(fetchedContactos) {

		const usuarios = fetchedContactos.map(usuario => {

			const template =
				`
					<li>
						<h2 translate="no">${usuario.nombre_usuario}</h2>

						<form method="POST" name="aceptarContacto">
							<input type="hidden" value="${usuario.id_usuario}" name="id_contacto">
							<button>
								<svg viewBox="0 0 800 800">
									<circle cx="400" cy="400" r="400"/>
									<path d="M173.52 490.2c-17.785-17.981-17.785-47.352 0-65.333l23.547-23.414a42.48 42.48 0 0 1 30.163-12.574 42.49 42.49 0 0 1 30.823 13.254l41.6 43.16c4.007 4.228 9.582 6.625 15.407 6.625s11.4-2.397 15.407-6.625l212-218.533a42.51 42.51 0 0 1 30.839-13.259c11.486 0 22.495 4.656 30.494 12.899l22.867 23.12c17.338 17.894 17.338 46.719 0 64.613L345.213 600.147c-7.998 8.279-19.03 12.961-30.542 12.961-11.319 0-22.18-4.525-30.151-12.561l-111-110.347z"/>
								</svg>
							</button>
						</form>

						<form method="POST" name="rechazarContacto">
							<input type="hidden" value="${usuario.id_usuario}" name="id_contacto">
							<button>
								<svg viewBox="0 0 800 800">
									<circle cx="400" cy="400" r="400"/>
									<path d="M600 532.68c8.522 8.515 13.316 20.08 13.316 32.127A45.43 45.43 0 0 1 600 596.933l-2.933 2.934c-17.684 17.657-46.757 17.657-64.44 0L415.773 483.133c-8.842-8.833-23.384-8.833-32.226 0L267 599.747a45.58 45.58 0 0 1-64.48 0l-2.587-2.574c-8.541-8.521-13.346-20.103-13.346-32.167a45.46 45.46 0 0 1 13.573-32.393l117.787-116.466a22.68 22.68 0 0 0 6.813-16.2c0-6.094-2.458-11.938-6.813-16.2L200.2 267.413a45.47 45.47 0 0 1-13.587-32.412c0-12.067 4.805-23.651 13.347-32.174l2.573-2.574c8.544-8.55 20.153-13.333 32.24-13.333 22.587 0 43.4 24.467 51.627 32.733l97.147 97.16a22.78 22.78 0 0 0 32.226 0l116.854-116.64c17.671-17.661 46.742-17.661 64.413 0l2.96 3.067a45.43 45.43 0 0 1 0 64.24L483.64 383.973c-4.261 4.251-6.658 10.028-6.658 16.047s2.397 11.796 6.658 16.047L600 532.68z"/>
								</svg>
							</button>
						</form>
					</li>
				`;

			return template;
		});

		return usuarios.join('');
	}

	async createUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CREAR);
		if (response.status === 201) {
			globalThis.location.href = 'sala-principal.php';
		}
	}

	async login(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGIN);
		if (response.status === 200) {
			globalThis.location.href = 'sala-principal.php';
		}
	}

	async logout(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.LOGOUT);
		if (response.status === 204) {
			globalThis.location.href = 'index.php';
		}
	}

	async deleteUsuario(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.DELETE);
		if (response.status === 204) {
			globalThis.location.href = 'registro.php';
		}
	}

	async cambiarNombre(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_NOMBRE);
		// if (response.status === 201) { }
	}

	async cambiarPassword(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.CAMBIAR_PASSWORD);
		// if (response.status === 201) { }
	}

	async solicitarContacto(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.SOLICITAR_CONTACTO);
		// if (response.status === 201) { }
	}

	async aceptarContacto(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.ACEPTAR);
		// if (response.status === 201) { }
	}

	async rechazarContacto(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.USUARIOS.RECHAZAR);
		// if (response.status === 201) { }
	}

	async sessionCheck() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.USUARIOS.CURRENT, 'get');
		this.usuario = response.json;
	}

	// MARK: STREAM CONTACTOS

	streamUsuarios() {

		const evtSource = new EventSource(this.ENDPOINTS.GET.USUARIOS.PENDIENTE);

		evtSource.addEventListener("usuario", (event) => {
			const contactos = JSON.parse(event.data);
			const content = this.usuariosTemplate(contactos);

			this.contactosMenu.innerHTML = this.sanitize(content);
		});
	}
}
