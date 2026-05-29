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

		const handlers = {
			'crear-usuario': (form) => this.createUsuario(form, 'post', this.ENDPOINTS.POST.USUARIOS.CREAR),
			'login': (form) => this.login(form, 'post', this.ENDPOINTS.POST.USUARIOS.LOGIN),
			'logout': (form) => this.logout(form, 'post', this.ENDPOINTS.POST.USUARIOS.LOGOUT),
			'delete-usuario': (form) => this.deleteUsuario(form, 'post', this.ENDPOINTS.POST.USUARIOS.DELETE),

			'crear-mensaje': (form) => this.writeMensaje(form, 'post', this.ENDPOINTS.POST.MENSAJES.CREAR),
			'eliminar-mensaje': (form) => this.deleteMensaje(form, 'post'),

			'crear-grupo': (form) => this.createGrupo(form, 'post', this.ENDPOINTS.POST.GRUPOS.CREAR),
			'aceptar-invitacion': (form) => this.aceptarInvitacion(form, 'post', this.ENDPOINTS.POST.GRUPOS.ACEPTAR_INVITACION),
			'invitar': (form) => this.invitar(form, 'post', this.ENDPOINTS.POST.GRUPOS.INVITAR)
		};

		forms.forEach(form => {
			form.onsubmit = (e) => {
				e.preventDefault();
				const handler = handlers[form.name];
				if (handler) handler(form);
			};
		});
	}
}
