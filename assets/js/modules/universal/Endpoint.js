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
				STREAM: `${this.ROOT}grupos/stream`,
				STREAM_NO_MIEMBRO: `${this.ROOT}grupos/no-miembro/stream`,
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
				CAMBIAR_NOMBRE: `${this.ROOT}usuarios/nombre`,
				CAMBIAR_PASSWORD: `${this.ROOT}usuarios/password`,
				SOLICITAR_CONTACTO: `${this.ROOT}usuarios/contacto`,
			},
			MENSAJES: {
				CREAR: `${this.ROOT}mensajes/crear`,
				ELIMINAR: `${this.ROOT}mensajes/delete`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`
			},
			GRUPOS: {
				CREAR: `${this.ROOT}grupos/crear`,
				INVITAR: `${this.ROOT}grupos/invitar`,
				ACEPTAR: `${this.ROOT}grupos/aceptar`,
				RECHAZAR: `${this.ROOT}grupos/rechazar`,
				ABANDONAR: `${this.ROOT}grupos/abandonar`,
				ELIMINAR: `${this.ROOT}grupos/delete`
			},
			CONEXION: {
				ESTADO: `${this.ROOT}conexion/estado`,
			}
		}
	};

	constructor() {
		super();
		this.initFormHandler();
		this.deleteErrorOutput();
	}

	initFormHandler() {
		document.onsubmit = (e) => {
			const form = e.target;
			if (form.tagName !== 'FORM') return;

			e.preventDefault();

			const name = form.name;

			typeof this[name] === 'function'
				? this[name](form)
				: console.warn(`No existe la función: ${name}`);
		};
	}


	deleteErrorOutput() {
		document.oninput = (e) => {
			const input = e.target;
			const form = input.closest('form');
			const output = form?.querySelector('output');

			if (output) output.innerHTML = '';
		};
	}

}
