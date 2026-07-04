import Grupo from "./Grupo.js";
import SSEManager from "./SSEManager.js";

export default class Invitacion extends Grupo {

	output = document.querySelector('output');
	invitacionesMenu = this.output.querySelector('menu:nth-of-type(1)');

	constructor() {
		super();

		// 🌟 SSE genérico para invitaciones
		this.invitacionesSSE = new SSEManager({
			restLoader: () => this.fetchData(null, this.ENDPOINTS.GET.INVITACIONES.LISTAR),
			sseUrl: this.ENDPOINTS.GET.INVITACIONES.STREAM,
			eventName: "invitacion",

			onRestData: (data) => {
				const html = this.invitacionesTemplate(data);
				this.invitacionesMenu.innerHTML = this.sanitize(html);
			},

			onSseData: (data) => {
				const html = this.invitacionesTemplate(data);
				this.invitacionesMenu.innerHTML = this.sanitize(html);
			},

			getLastIdFromRest: (data) => data.length ? data[data.length - 1].id : null,
			getIdFromSse: (data) => data.id
		});

		this.invitacionesSSE.start();
	}

	// 🌟 SSE genérico para contactos invitables
	streamContactosInvitables(idGrupo) {

		this.contactosSSE?.stop(); // cerrar si ya existía

		this.contactosSSE = new SSEManager({
			restLoader: () => this.fetchData(null, `${this.ENDPOINTS.GET.INVITACIONES.CONTACTOS_INVITABLES}?ulid_grupo=${idGrupo}`),
			sseUrl: `${this.ENDPOINTS.GET.INVITACIONES.CONTACTOS_INVITABLES}?ulid_grupo=${idGrupo}`,
			eventName: "no miembro",

			onRestData: (data) => {
				const html = this.contactosInvitablesTemplate(data);
				document.getElementById('ulid_contacto').innerHTML = this.sanitize(html);
			},

			onSseData: (data) => {
				const html = this.contactosInvitablesTemplate(data);
				document.getElementById('ulid_contacto').innerHTML = this.sanitize(html);
			},

			getLastIdFromRest: (data) => data.length ? data[data.length - 1].id : null,
			getIdFromSse: (data) => data.id
		});

		this.contactosSSE.start();
	}
}
