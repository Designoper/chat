import Usuario from "./Usuario.js";

export default class Contacto extends Usuario {
	output = document.querySelector('output');
	contactosMenu = this.output.querySelector('menu:nth-of-type(2)');

	constructor() {
		super();
	}

	// MARK: CONTACTOS TEMPLATE

	contactosTemplate(fetchedContactos) {

		const contactos = fetchedContactos.map(contacto => {

			let id;
			let imgSrc;

			switch (contacto.tipo) {
				case 'usuario':
					id = 'id_receptor';
					imgSrc =
						`
						<svg viewBox="0 0 800 800">
							<path d="M.001 823.889c0-220.889 179.111-400 400-400s400 179.111 400 400-179.111 400-400 400-400-179.111-400-400zm212.11-636C212.111 84.132 296.244 0 400 0s187.889 84.132 187.889 187.889S503.756 375.777 400 375.777s-187.888-84.132-187.888-187.889z"/>
						</svg>
					`;

					break;
				case 'grupo':
					id = 'id_grupo';
					imgSrc =
						`
						<svg viewBox="0 0 3544 1772">
							<path d="M1380.657 2652.62c-163.989 179.332-399.911 291.913-661.935 291.913-495.156 0-896.661-401.505-896.661-896.661s401.505-896.661 896.661-896.661c141.274 0 274.777 32.678 393.734 90.862 163.989-179.332 399.712-291.913 661.935-291.913 261.626 0 496.949 112.182 660.939 290.917 118.359-57.586 251.065-89.865 391.542-89.865 495.156 0 896.661 401.505 896.661 896.661s-401.505 896.661-896.661 896.661c-261.626 0-497.149-111.983-660.939-290.717-118.359 57.386-251.264 89.666-391.542 89.666-141.274 0-274.976-32.678-393.734-90.862zM2405.641 622.181c0-232.534 188.697-421.231 421.231-421.231s421.231 188.697 421.231 421.231-188.697 421.231-421.231 421.231-421.231-188.697-421.231-421.231zm-2108.15 0c0-232.534 188.498-421.231 421.231-421.231 232.534 0 421.032 188.697 421.032 421.231s-188.498 421.231-421.032 421.231c-232.733 0-421.231-188.697-421.231-421.231zM1353.16 421.13c0-232.534 188.498-421.032 421.231-421.032 232.534 0 421.032 188.498 421.032 421.032 0 232.733-188.498 421.231-421.032 421.231-232.733 0-421.231-188.498-421.231-421.231z"/>
						</svg>
					`;
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
						${imgSrc}
						<h2 translate="no">${contacto.nombre}</h2>
						${badge}
						${lastMessage}
					</a>
				</li>`;

			return template;
		});

		return contactos.join('');
	}

	// MARK: STREAM CONTACTOS

	streamContactos() {

		const evtSource = new EventSource(this.ENDPOINTS.GET.CONTACTOS.STREAM);

		evtSource.addEventListener("new update", (event) => {
			const contactos = JSON.parse(event.data);
			const content = this.contactosTemplate(contactos);

			this.contactosMenu.innerHTML = this.sanitize(content);
		});
	}
}