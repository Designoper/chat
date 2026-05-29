import Fetch from "./Fetch.js";

export default class Endpoint extends Fetch {
	ROOT = `${location.origin}/api/`;

	ENDPOINTS = {
		GET: {
			USUARIOS: {
				OTROS: `${this.ROOT}usuarios`,
				CURRENT: `${this.ROOT}usuarios/current`,
			},
			MENSAJES: {
				TODOS: `${this.ROOT}mensajes`,
				STREAM: `${this.ROOT}mensajes/stream`,
				NO_LEIDOS: `${this.ROOT}mensajes/no-leidos`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`,
			},
			GRUPOS: {
				MIEMBRO: `${this.ROOT}grupos/miembro`,
				PENDIENTE: `${this.ROOT}grupos/pendiente`,
				NO_MIEMBRO: `${this.ROOT}grupos/no-miembro`,
			},
			CONEXION: {
				STREAM: `${this.ROOT}conexion/stream`,
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
				ELIMINAR: `${this.ROOT}mensajes`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`
			},
			GRUPOS: {
				CREAR: `${this.ROOT}grupos/crear`,
				INVITAR: `${this.ROOT}grupos/invitar`,
				ACEPTAR_INVITACION: `${this.ROOT}grupos/aceptar`
			},
			CONEXION: {
				ESTADO: `${this.ROOT}conexion/estado`,
			}
		}
	};

	constructor() {
		super();
	}

	formHandler() {
		const forms = document.querySelectorAll('form');

		forms.forEach(form => {
			form.onsubmit = (e) => {
				e.preventDefault();

				const name = form.name;

				typeof this[name] === 'function'
					? this[name](form)
					: console.warn(`No existe la función: ${name}`);
			};
		});
	}
}
