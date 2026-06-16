import Usuario from "./Usuario.js";

export default class Contacto extends Usuario {
	output = document.querySelector('output');
	contactosMenu = this.output.querySelector('menu:nth-of-type(2)');

	constructor() {
		super();
	}

	// async printContactos() {
	// 	const contactos = await this.getContactos();
	// 	const usuariosPrint = this.contactosTemplate(contactos);

	// 	this.contactosMenu.setHTML(`${usuariosPrint}`, {
	// 		sanitizer: new Sanitizer({
	// 			comments: false,
	// 		})
	// 	});
	// }

	// async getContactos() {
	// 	const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.CONTACTOS.TODOS, 'get');
	// 	return response.json;
	// }

	contactosTemplate(fetchedContactos) {

		const contactos = fetchedContactos.map(contacto => {

			let id;
			let imgSrc;

			switch (contacto.tipo) {
				case 'usuario':
					id = 'id_receptor';
					imgSrc = "../../assets/img/user.svg";

					break;
				case 'grupo':
					id = 'id_grupo';
					imgSrc = "../../assets/img/group.svg";
			}

			const badge = contacto.num_mensajes > 0
				? `<data>${contacto.num_mensajes}</data>`
				: '';

			const autorMensaje = contacto.id_emisor === this.usuario.id_usuario
				? 'Tú'
				: `<span translate="no">${contacto.nombre}</span>`;

			const lastMessage = contacto.contenido
				? `<date>${this.compareTime(contacto.fecha_envio, false)}</date>
					<p>${autorMensaje}: ${contacto.contenido}</p>`
				: '';

			const template =
				`<li>
					<a href="./chat.php?${id}=${contacto.id}&nombre=${contacto.nombre}">
						<img src="${imgSrc}">
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

			// this.contactosMenu.setHTML(content, {this.sanitizeDefault4});
			this.contactosMenu.setHTML(content, { sanitizer: new Sanitizer({}) });
		});
	}
}