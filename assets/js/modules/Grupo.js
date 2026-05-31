import Endpoint from "./Endpoint.js";

export default class Grupo extends Endpoint {
	outputMiembro = document.querySelector('output>menu>li:nth-of-type(4) menu');
	outputPendiente = document.querySelector('output>menu>li:nth-of-type(5) menu');

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
					`<li>

						<h3 translate="no">${grupo.nombre_grupo}</h3>

						<a href="./chat.php?id_grupo=${grupo.id_grupo}&nombre_grupo=${grupo.nombre_grupo}">
							<svg viewBox="0 0 2481 2481">
								<path d="M573.027 1811.925h-407.68c-90.945 0-165.355-85.823-165.355-190.725V190.712C-.008 85.824 74.402-.013 165.347-.013h2149.601c90.953 0 165.357 85.837 165.357 190.725V1621.2c0 104.901-74.403 190.725-165.357 190.725H1310.686l-709.001 649.723c-23.693 21.712-58.535 25.996-86.787 10.696-28.251-15.313-43.669-46.856-38.414-78.56l96.543-581.859zm180.208-905.916c0-115.278-93.509-208.712-208.706-208.712-115.212 0-208.714 93.433-208.714 208.712s93.501 208.712 208.714 208.712c115.198 0 208.706-93.433 208.706-208.712zm695.688 0c0-115.278-93.494-208.712-208.706-208.712s-208.706 93.433-208.706 208.712 93.494 208.712 208.706 208.712 208.706-93.433 208.706-208.712zm695.688 0c0-115.278-93.494-208.712-208.706-208.712-115.198 0-208.706 93.433-208.706 208.712s93.509 208.712 208.706 208.712c115.212 0 208.706-93.433 208.706-208.712z"/>
							</svg>
						</a>
						<span>${badge}</span>

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
			`<li>
				<h3 translate="no">${grupo.nombre_grupo}</h3>
				<form method="POST" name="aceptarInvitacion">
					<input type="hidden" value="${grupo.id_grupo}" name="id_grupo">
					<button>Aceptar</button>
				</form>
			</li>`
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