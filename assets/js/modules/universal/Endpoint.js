import Fetch from "./Fetch.js";

export default class Endpoint extends Fetch {
	ROOT = `${location.origin}/api/`;

	ENDPOINTS = {
		GET: {
			USUARIOS: {
				CURRENT: `${this.ROOT}usuarios/current`,
			},
			INVITACIONES: {
				CONTACTOS: `${this.ROOT}invitaciones/contactos`,
				CONTACTOS_INVITABLES: `${this.ROOT}invitaciones/contactos-invitables`,
				GRUPOS: `${this.ROOT}invitaciones/grupos`,
			},
			CONTACTOS: {
				STREAM: `${this.ROOT}contactos/stream`,
			},
			MENSAJES: {
				TODOS: `${this.ROOT}mensajes`,
				STREAM: `${this.ROOT}mensajes/stream`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`,
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
				CAMBIAR_NOMBRE: `${this.ROOT}usuarios/nombre`,
				CAMBIAR_PASSWORD: `${this.ROOT}usuarios/password`,
			},
			GRUPOS: {
				CREAR: `${this.ROOT}grupos/crear`,
				ELIMINAR: `${this.ROOT}grupos/delete`,
				ABANDONAR: `${this.ROOT}grupos/abandonar`,
			},
			INVITACIONES: {
				INVITAR_CONTACTO: `${this.ROOT}invitaciones/usuarios/invitar`,
				ACEPTAR_CONTACTO: `${this.ROOT}invitaciones/usuarios/aceptar`,
				RECHAZAR_CONTACTO: `${this.ROOT}invitaciones/usuarios/rechazar`,
				INVITAR_GRUPO: `${this.ROOT}invitaciones/grupos/invitar`,
				ACEPTAR_GRUPO: `${this.ROOT}invitaciones/grupos/aceptar`,
				RECHAZAR_GRUPO: `${this.ROOT}invitaciones/grupos/rechazar`,
			},
			MENSAJES: {
				CREAR: `${this.ROOT}mensajes/crear`,
				ELIMINAR: `${this.ROOT}mensajes/delete`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`
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
