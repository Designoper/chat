import Endpoint from "./Endpoint.js";

export default class Grupo extends Endpoint {
	outputMiembro = document.querySelector('output');
	outputPendiente = document.querySelector('section:nth-of-type(2) output');

	constructor() {
		super();
	}

	async getGruposMiembro() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.MIEMBRO, 'get');
		this.printGruposMiembro(response);
	}

	async getGruposPendiente() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.GRUPOS.PENDIENTE, 'get');
		this.printGruposPendiente(response);
	}

	async printGruposMiembro(grupos) {
		const content = await this.gruposMiembroTemplate(grupos.content);
		this.outputMiembro.innerHTML = content;
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

				const num = mensajesNoLeidos.content.num_mensajes;

				const badge = num > 0
					? `(${num})`
					: '';

				const opciones = invitables.content
					.map(user => `<option translate="no" value="${user.id_usuario}">${user.nombre_usuario}</option>`)
					.join('');

				const formInvitar =
					`<article>

						<h3 translate="no">${grupo.nombre_grupo}</h3>

						<form method="POST" name="invitar">
							<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
							<select name="id_usuario" required>
								<option value="">Invitar a...</option>
								${opciones}
							</select>
							<button>Mandar invitación</button>
						</form>

						<a href="./chat.php?id_grupo=${grupo.id_grupo}&nombre_grupo=${grupo.nombre_grupo}">Entrar</a>
						<span>${badge}</span>

					</article>`;

				return formInvitar;

			})
		);

		return grupos.join('');
	}

	gruposPendienteTemplate(fetchedGrupos) {

		const grupos = fetchedGrupos.map(grupo =>
			`<article>
				<h3 translate="no">${grupo.nombre_grupo}</h3>
				<form method="POST" name="aceptarInvitacion">
					<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
					<button>Aceptar invitación</button>
				</form>
			</article>`
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
}