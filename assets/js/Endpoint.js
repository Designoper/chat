import { Fetch } from "./Fetch.js";

export class Endpoint extends Fetch {
	ROOT = `${location.origin}/api/`;

	ENDPOINTS = {

		// GET

		GET_MENSAJES: `${this.ROOT}mensajes`,
		GET_MENSAJES_DIRECTOS: `${this.ROOT}mensajes-directos`,
		GET_USUARIOS: `${this.ROOT}usuarios`,

		// POST

		CREAR_USUARIOS: `${this.ROOT}usuarios/crear`,
		LOGIN_USUARIOS: `${this.ROOT}usuarios/login`,
		LOGOUT_USUARIOS: `${this.ROOT}usuarios/logout`,
		CURRENT_USUARIOS: `${this.ROOT}usuarios/current`,
		// USUARIO_RECEPTOR: `${this.ROOT}usuarios/receptor`,
		CREAR_MENSAJES: `${this.ROOT}mensajes/crear`,
		CREAR_MENSAJES_DIRECTOS: `${this.ROOT}mensajes-directos/crear`,
		ELIMINAR_MENSAJES: `${this.ROOT}mensajes`,
	};

	constructor() {
		super();
	}

	formHandler() {
		const forms = document.querySelectorAll('form');

		forms.forEach(form => {
			form.onsubmit = (submitEvent) => {
				submitEvent.preventDefault();
				const name = form.name;

				switch (name) {
					case 'crear-usuario':
						this.createUsuario(form, 'post', this.ENDPOINTS.CREAR_USUARIOS);
						break;

					case 'login-usuario':
						this.loginUsuario(form, 'post', this.ENDPOINTS.LOGIN_USUARIOS);
						break;

					case 'logout-usuario':
						this.logout(form, 'post', this.ENDPOINTS.LOGOUT_USUARIOS);
						break;

					case 'crear-mensaje':
						this.writeMensaje(form, 'post', this.ENDPOINTS.CREAR_MENSAJES);
						break;

					case 'crear-mensaje-directo':
						this.writeMensajeDirecto(form, 'post', this.ENDPOINTS.CREAR_MENSAJES_DIRECTOS);
						break;

					// case 'usuario-receptor':
					// 	this.usuarioReceptor(form, 'post', this.ENDPOINTS.USUARIO_RECEPTOR);
					// 	break;

					case 'eliminar-mensaje':
						this.deleteMensaje(form, 'post');
						break;
				}
			}
		});
	}
}
