import Fetch from "./Fetch.js";

export default class Endpoint extends Fetch {
	ROOT = `${location.origin}/api/`;

	ENDPOINTS = {

		// GET

		GET_MENSAJES: `${this.ROOT}mensajes`,
		GET_MENSAJES_DIRECTOS: `${this.ROOT}mensajes-directos`,
		GET_MENSAJES_GRUPALES: `${this.ROOT}mensajes-grupales`,
		GET_USUARIOS: `${this.ROOT}usuarios`,
		GET_GRUPOS: `${this.ROOT}grupos`,
		GET_GRUPOS_MIEMBRO: `${this.ROOT}grupos/miembro`,
		GET_GRUPOS_PENDIENTE: `${this.ROOT}grupos/pendiente`,
		GET_GRUPOS_NO_MIEMBRO: `${this.ROOT}grupos/no-miembro`,

		// POST

		CREAR_USUARIOS: `${this.ROOT}usuarios/crear`,
		LOGIN_USUARIOS: `${this.ROOT}usuarios/login`,
		LOGOUT_USUARIOS: `${this.ROOT}usuarios/logout`,
		CURRENT_USUARIOS: `${this.ROOT}usuarios/current`,
		DELETE_USUARIOS: `${this.ROOT}usuarios/delete`,
		CREAR_MENSAJES: `${this.ROOT}mensajes/crear`,
		CREAR_MENSAJES_DIRECTOS: `${this.ROOT}mensajes-directos/crear`,
		CREAR_MENSAJES_GRUPALES: `${this.ROOT}mensajes-grupales/crear`,
		ELIMINAR_MENSAJES: `${this.ROOT}mensajes`,
		CREAR_GRUPOS: `${this.ROOT}grupos/crear`,
		INVITAR: `${this.ROOT}grupos/invitar`,
		ACEPTAR_INVITACION: `${this.ROOT}grupos/aceptar`
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

					case 'delete-usuario':
						this.deleteUsuario(form, 'post', this.ENDPOINTS.DELETE_USUARIOS);
						break;

					case 'crear-mensaje':
						this.writeMensaje(form, 'post', this.ENDPOINTS.CREAR_MENSAJES);
						break;

					case 'crear-mensaje-directo':
						this.writeMensajeDirecto(form, 'post', this.ENDPOINTS.CREAR_MENSAJES_DIRECTOS);
						break;

					case 'crear-mensaje-grupal':
						this.writeMensajeGrupal(form, 'post', this.ENDPOINTS.CREAR_MENSAJES_GRUPALES);
						break;

					case 'eliminar-mensaje':
						this.deleteMensaje(form, 'post');
						break;

					case 'crear-grupo':
						this.createGrupo(form, 'post', this.ENDPOINTS.CREAR_GRUPOS);
						break;

					case 'aceptar-invitacion':
						this.aceptarInvitacion(form, 'post', this.ENDPOINTS.ACEPTAR_INVITACION);
						break;

					case 'invitar':
						this.invitar(form, 'post', this.ENDPOINTS.INVITAR);
						break;
				}
			}
		});
	}
}
