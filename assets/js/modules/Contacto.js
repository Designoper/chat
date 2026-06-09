import Usuario from "./Usuario.js";

export default class Contacto extends Usuario {
	output = document.querySelector('output');
	contactosMenu = this.output.querySelector('menu:nth-of-type(2)');

	constructor() {
		super();
	}

	async printContactos() {
		// const gruposMiembro = await this.getGruposMiembro();
		// const gruposMiembroPrint = await this.gruposMiembroTemplate(gruposMiembro);

		// const gruposPendiente = await this.getGruposPendiente();
		// const gruposPendientePrint = this.gruposPendienteTemplate(gruposPendiente);

		const contactos = await this.getContactos();
		const usuariosPrint = this.contactosTemplate(contactos);

		// this.output.setHTML(`${usuariosPrint}${gruposMiembroPrint}${gruposPendientePrint}`, {
		// 	sanitizer: new Sanitizer({
		// 		comments: false,
		// 	})
		// });

		this.contactosMenu.setHTML(`${usuariosPrint}`, {
			sanitizer: new Sanitizer({
				comments: false,
			})
		});
	}

	async getContactos() {
		const { json } = await this.fetchWithoutForm(this.ENDPOINTS.GET.CONTACTOS.TODOS, 'get');
		return json;
	}

	contactosTemplate(fetchedContactos) {

		const contactos = fetchedContactos.map(contacto => {

			let id;

			switch (contacto.tipo) {
				case 'usuario':
					id = 'id_receptor';
					break;
				case 'grupo':
					id = 'id_grupo';
			}

			const badge = contacto.num_mensajes > 0
				? `<data>${contacto.num_mensajes}</data>`
				: '';

			const autorMensaje = contacto.id_emisor === this.usuario.id_usuario
				? 'Tú'
				: `<span translate="no">${contacto.nombre}</span>`;

			const lastMessage = contacto.contenido
				? `<date>${this.compareTime(contacto.fecha_envio)}</date>
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

		return contactos.join('');
	}

	streamContactos() {

		const evtSource = new EventSource(this.ENDPOINTS.GET.CONTACTOS.STREAM);

		evtSource.addEventListener("new update", (event) => {
			const contactos = JSON.parse(event.data);
			const content = this.contactosTemplate(contactos);

			this.contactosMenu.setHTML(`${content}`, {
				sanitizer: new Sanitizer({
					comments: false,
				})
			});
		});
	}
}