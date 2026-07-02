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
				STREAM: `${this.ROOT}invitaciones/stream`,
			},
			CONTACTOS: {
				STREAM: `${this.ROOT}contactos/stream`,
			},
			MENSAJES: {
				DIRECTOS: `${this.ROOT}mensajes/directos`,
				GRUPALES: `${this.ROOT}mensajes/grupales`,
				STREAM_DIRECTOS: `${this.ROOT}mensajes/stream/directos`,
				STREAM_GRUPALES: `${this.ROOT}mensajes/stream/grupales`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`,
				ARCHIVOS: {
					DIRECTO: `${this.ROOT}mensajes/archivos/directo`,
					GRUPAL: `${this.ROOT}mensajes/archivos/grupal`,
				},
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
				CREAR_DIRECTO: `${this.ROOT}mensajes/crear/directo`,
				CREAR_IMAGEN_DIRECTO: `${this.ROOT}mensajes/crear/imagen-directo`,
				CREAR_AUDIO_DIRECTO: `${this.ROOT}mensajes/crear/audio-directo`,
				CREAR_GRUPAL: `${this.ROOT}mensajes/crear/grupal`,
				CREAR_IMAGEN_GRUPAL: `${this.ROOT}mensajes/crear/imagen-grupal`,
				CREAR_AUDIO_GRUPAL: `${this.ROOT}mensajes/crear/audio-grupal`,
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
