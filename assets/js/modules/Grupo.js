import Usuario from "./Usuario.js";

export default class Grupo extends Usuario {
	output = document.querySelector('output>menu');

	constructor() {
		super();
	}

	async finalPrint() {
		const gruposMiembro = await this.getGruposMiembro();
		const gruposMiembroPrint = await this.gruposMiembroTemplate(gruposMiembro);

		const gruposPendiente = await this.getGruposPendiente();
		const gruposPendientePrint = this.gruposPendienteTemplate(gruposPendiente);

		const usuarios = await this.getUsuarios();
		const usuariosPrint = await this.usuariosTemplate(usuarios);

		this.output.setHTML(`${usuariosPrint}${gruposMiembroPrint}${gruposPendientePrint}`, { sanitizer: new Sanitizer({}) });
	}

	async getGruposMiembro() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.MIEMBRO, 'get');
		return response.content;
	}

	async getGruposPendiente() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.PENDIENTE, 'get');
		return response.content;
	}

	async printGruposMiembro(grupos) {
		const content = await this.gruposMiembroTemplate(grupos.content);
		return response.content;
	}

	printGruposPendiente(grupos) {
		const content = this.gruposPendienteTemplate(grupos.content);
		this.outputPendiente.innerHTML = content;
	}

	async gruposMiembroTemplate(fetchedGrupos) {

		const grupos = await Promise.all(
			fetchedGrupos.map(async grupo => {

				const invitables = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.NO_MIEMBRO, 'get',
					{
						"id_grupo": grupo.id_grupo
					}
				);

				const mensajesNoLeidos = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.NO_LEIDOS, 'get',
					{
						"id_grupo": grupo.id_grupo
					}
				);

				const ultimoMensaje = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.ULTIMO_MENSAJE, 'get',
					{
						"id_grupo": grupo.id_grupo
					}
				);

				const autorMensaje = ultimoMensaje?.content?.id_emisor === this.id_usuario
					? 'Tú'
					: `<span translate="no">${ultimoMensaje.content.nombre_usuario}</span>`;

				const lastMessage = ultimoMensaje?.content?.contenido
					? `<date>${this.fullDate(ultimoMensaje.content.fecha_envio)}</date>
						<p>${autorMensaje}: ${ultimoMensaje.content.contenido}</p>`
					: '';

				const badge = mensajesNoLeidos.content.num_mensajes > 0
					? `<data>${mensajesNoLeidos.content.num_mensajes}</data>`
					: '';

				const opciones = invitables.content
					.map(user => `<option translate="no" value="${user.id_usuario}">${user.nombre_usuario}</option>`)
					.join('');

				const formInvitar =
					`<li>

						<a href="./chat.php?id_grupo=${grupo.id_grupo}&nombre_grupo=${grupo.nombre_grupo}">
							<h2 translate="no">${grupo.nombre_grupo}</h2>
							${badge}
							${lastMessage}
						</a>

						<form method="POST" name="invitar">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<select name="id_usuario" required>
								<option value="">Añadir a...</option>
								${opciones}
							</select>
							<button>Invitar</button>
						</form>

					</li>`;

				return formInvitar;

			})
		);

		return grupos.join('');
	}

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`
			< li >
				<h2 translate="no">${grupo.nombre_grupo}</h2>
				<form method="POST" name="aceptarInvitacion">
					<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
					<button>Aceptar</button>
				</form>
			</li >
			`
		).join('');

		return grupos;
	}

	async createGrupo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.CREAR);
		if (response.status === 201) {
			await this.getGruposMiembro();
			await this.getGruposPendiente();
		}
	}

	async invitar(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.INVITAR);
		if (response.status === 201) {
			await this.getGruposMiembro();
			await this.getGruposPendiente();
		}
	}

	async aceptarInvitacion(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.GRUPOS.ACEPTAR_INVITACION);
		if (response.status === 200) {
			await this.getGruposMiembro();
			await this.getGruposPendiente();
		}
	}
};;