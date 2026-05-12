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
				STREAM: `${this.ROOT}stream-mensajes`,
				NO_LEIDOS: `${this.ROOT}mensajes/no-leidos`,
			},
			GRUPOS: {
				GRUPOS: `${this.ROOT}grupos`,
				MIEMBRO: `${this.ROOT}grupos/miembro`,
				PENDIENTE: `${this.ROOT}grupos/pendiente`,
				NO_MIEMBRO: `${this.ROOT}grupos/no-miembro`,
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
				CREAR_DIRECTO: `${this.ROOT}mensajes-directos/crear`,
				CREAR_GRUPAL: `${this.ROOT}mensajes-grupales/crear`,
				ELIMINAR: `${this.ROOT}mensajes`,
				ULTIMO_ID: `${this.ROOT}mensajes/ultimo-id`,
			},
			GRUPOS: {
				CREAR: `${this.ROOT}grupos/crear`,
				INVITAR: `${this.ROOT}grupos/invitar`,
				ACEPTAR_INVITACION: `${this.ROOT}grupos/aceptar`
			}
		}
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
						this.createUsuario(form, 'post', this.ENDPOINTS.POST.USUARIOS.CREAR);
						break;

					case 'login-usuario':
						this.loginUsuario(form, 'post', this.ENDPOINTS.POST.USUARIOS.LOGIN);
						break;

					case 'logout-usuario':
						this.logout(form, 'post', this.ENDPOINTS.POST.USUARIOS.LOGOUT);
						break;

					case 'delete-usuario':
						this.deleteUsuario(form, 'post', this.ENDPOINTS.POST.USUARIOS.DELETE);
						break;

					case 'crear-mensaje':
						this.writeMensaje(form, 'post', this.ENDPOINTS.POST.MENSAJES.CREAR);
						break;

					case 'crear-mensaje-directo':
						this.writeMensajeDirecto(form, 'post', this.ENDPOINTS.POST.MENSAJES.CREAR_DIRECTO);
						break;

					case 'crear-mensaje-grupal':
						this.writeMensajeGrupal(form, 'post', this.ENDPOINTS.POST.MENSAJES.CREAR_GRUPAL);
						break;

					case 'eliminar-mensaje':
						this.deleteMensaje(form, 'post');
						break;

					case 'crear-grupo':
						this.createGrupo(form, 'post', this.ENDPOINTS.POST.GRUPOS.CREAR);
						break;

					case 'aceptar-invitacion':
						this.aceptarInvitacion(form, 'post', this.ENDPOINTS.POST.GRUPOS.ACEPTAR_INVITACION);
						break;

					case 'invitar':
						this.invitar(form, 'post', this.ENDPOINTS.POST.GRUPOS.INVITAR);
						break;
				}
			}
		});
	}
}
