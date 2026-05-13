import Usuario from "./Usuario.js";

export default class Conexion extends Usuario {

	urlStream = new URL(this.ENDPOINTS.GET.CONEXION.STREAM);
	url = new URL(location.href);

	endpointConexion = this.ENDPOINTS.POST.CONEXION.ESTADO;

	// h1 = document.querySelector('h1');
	input = document.querySelector('input[type="hidden"]');

	id_receptor = this.url.searchParams.get('id-receptor');
	nombre_receptor = this.url.searchParams.get('nombre-receptor');

	id_grupo = this.url.searchParams.get('id-grupo');
	nombre_grupo = this.url.searchParams.get('nombre-grupo');

	constructor() {
		super();

		// Cuando la pestaña se cierra o navega → desconectado
		window.addEventListener("pagehide", () => {
			const data = new FormData();
			data.append("estado", "0");
			navigator.sendBeacon(this.endpointConexion, data);
		});
	}

	async setConexion() {
		const response = await this.fetchPostNoForm(this.endpointConexion, {
			"estado": "1"
		});
	}

	// async getMensajes(params = {}) {
	// 	const response = await this.simpleFetch(this.endpointMensaje, params);
	// 	const mensajes = this.mensajesTemplate(response.content);
	// 	this.MENSAJES_OUTPUT.innerHTML = mensajes;
	// 	this.formHandler();

	// 	let id;

	// 	response.content.length > 0
	// 		? id = response.content[response.content.length - 1].id_mensaje
	// 		: id = "";

	// 	params.ultimo_id = id;
	// 	this.setData(params);

	// 	await this.fetchPostNoForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, this.obj);
	// }

	// streamConexion() {

	// const evtSource = new EventSource(this.urlStream);

	// evtSource.addEventListener("mensaje", (event) => {
	// const mensaje = JSON.parse(event.data);
	// const content = this.mensajesTemplate([mensaje]);

	// this.MENSAJES_OUTPUT.insertAdjacentHTML("beforeend", content);
	// this.formHandler();
	// });

	// evtSource.addEventListener("new mensaje", async (event) => {
	// 	const id = JSON.parse(event.data);

	// 	this.obj.ultimo_id = id;

	// 	const response = await this.fetchPostNoForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, this.obj);
	// });
	// }
}
