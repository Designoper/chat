import { Fetch } from "./Fetch.js";

export class Endpoint extends Fetch {
	ROOT = `${location.protocol}//${location.host}/api/`;

	ENDPOINTS = {
		CREAR_USUARIOS: `${this.ROOT}usuarios/crear`,
		LOGIN_USUARIOS: `${this.ROOT}usuarios/login`,
		LOGOUT_USUARIOS: `${this.ROOT}usuarios/logout`,
		CURRENT_USUARIOS: `${this.ROOT}usuarios/current`,
		GET_MENSAJES: `${this.ROOT}mensajes`,
		CREAR_MENSAJES: `${this.ROOT}mensajes/crear`,
		ELIMINAR_MENSAJES: `${this.ROOT}mensajes`
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
					case 'eliminar-mensaje':
						this.deleteMensaje(form, 'post');
						break;
				}
			}
		});
	}
}
