import Fetch from "./Fetch.js";

export default class Endpoint extends Fetch {
	ROOT = `${location.origin}/api/`;

	ENDPOINTS = {
		GET: {
			USUARIOS: {
				OTROS: `${this.ROOT}usuarios`,
				STREAM: `${this.ROOT}usuarios/stream`,
				CURRENT: `${this.ROOT}usuarios/current`,
			},
			MENSAJES: {
				TODOS: `${this.ROOT}mensajes`,
				STREAM: `${this.ROOT}mensajes/stream`,
				NO_LEIDOS: `${this.ROOT}mensajes/no-leidos`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`,
				ULTIMO_MENSAJE: `${this.ROOT}mensajes/ultimo-mensaje`
			},
			GRUPOS: {
				MIEMBRO: `${this.ROOT}grupos/miembro`,
				PENDIENTE: `${this.ROOT}grupos/pendiente`,
				NO_MIEMBRO: `${this.ROOT}grupos/no-miembro`,
			},
			CONEXION: {
				STREAM: `${this.ROOT}conexion/stream`,
			},
			CONTACTOS: {
				TODOS: `${this.ROOT}contactos`,
				STREAM: `${this.ROOT}contactos/stream`,
			}
		},
		POST: {
			USUARIOS: {
				CREAR: `${this.ROOT}usuarios/crear`,
				LOGIN: `${this.ROOT}usuarios/login`,
				LOGOUT: `${this.ROOT}usuarios/logout`,
				DELETE: `${this.ROOT}usuarios/delete`,
			},
			MENSAJES: {
				CREAR: `${this.ROOT}mensajes/crear`,
				ELIMINAR: `${this.ROOT}mensajes/delete`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`
			},
			GRUPOS: {
				CREAR: `${this.ROOT}grupos/crear`,
				INVITAR: `${this.ROOT}grupos/invitar`,
				ACEPTAR_INVITACION: `${this.ROOT}grupos/aceptar`,
				RECHAZAR_INVITACION: `${this.ROOT}grupos/rechazar`
			},
			CONEXION: {
				ESTADO: `${this.ROOT}conexion/estado`,
			}
		}
	};

	constructor() {
		super();
		this.initFormHandler();
	}

	// sanitizeForm(form) {

	// 	const strictSanitizer = new Sanitizer();
	// 	const campos = form.querySelectorAll("input, textarea");

	// 	campos.forEach(campo => {
	// 		const valor = campo.value;
	// 		campo.value = strictSanitizer.sanitize(valor);
	// 	});
	// }

	initFormHandler() {
		document.addEventListener('submit', (e) => {
			const form = e.target;
			if (form.tagName !== 'FORM') return;
			// const j = new HTMLFormElement;

			e.preventDefault();
			// this.sanitizeForm(form);

			const name = form.name;

			typeof this[name] === 'function'
				? this[name](form)
				: console.warn(`No existe la función: ${name}`);
		});
	}
}
