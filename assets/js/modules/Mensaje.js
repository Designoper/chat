import Contacto from "./Contacto.js";

export default class Mensaje extends Contacto {

	urlStreamMensajes = new URL(this.ENDPOINTS.GET.MENSAJES.STREAM);
	endpointMensaje = this.ENDPOINTS.GET.MENSAJES.TODOS;

	urlSearchParams = new URLSearchParams(location.search);
	ringtone = new Audio("../../../assets/audio/ringtone.mp3");

	mostrado = false;

	dom = {
		header: document.querySelector('header'),
		h1: document.querySelector('h1'),
		output: document.querySelector('output'),
		form: document.querySelector('form'),
	};

	mensaje = {};

	ultimoIdLeido;

	constructor() {
		super();
	}

	// MARK: GET ULTIMO ID

	async getUltimoId() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.ULTIMO_ID, 'get', this.urlSearchparamsObj);
		this.ultimoIdLeido = response.json.id_mensaje;
	}

	delete() {
		this.urlSearchParams.delete('nombre');
	}

	setObj() {
		this.urlSearchparamsObj = Object.fromEntries(this.urlSearchParams);
	}

	setForm() {
		for (const [key, value] of this.urlSearchParams.entries()) {
			const hiddenInput = `<input type="hidden" name="${key}" value="${value}">`;
			this.dom.form.insertAdjacentHTML('afterbegin', this.sanitize(hiddenInput));
		}
	}

	// MARK: SCROLL TO CURRENT

	scrollToCurrent() {
		const marcador = this.dom.output.querySelector("#marcador");

		marcador
			? marcador.scrollIntoView({
				behavior: "instant",
				block: "center"
			})
			: globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
	}

	// MARK: GET MENSAJES

	async getMensajes() {
		const response = await this.fetchWithoutForm(this.endpointMensaje, 'get', this.urlSearchparamsObj);

		if (response.json.length > 0) {
			this.urlSearchparamsObj.id_mensaje = response.json[response.json.length - 1].id_mensaje;
			await this.fetchWithoutForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', this.urlSearchparamsObj);
		}

		const mensajes = this.mensajesTemplate(response.json);
		this.dom.output.innerHTML = this.sanitize(mensajes);
	}

	// MARK: STREAM MENSAJES

	streamMensajes() {

		this.urlStreamMensajes.search = this.urlSearchParams;
		const evtSource = new EventSource(this.urlStreamMensajes);
		this.mostrado = true;

		evtSource.addEventListener("mensaje", (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);

			this.dom.output.insertAdjacentHTML("beforeend", this.sanitize(content));
			this.ringtone.play();
		});

		evtSource.addEventListener("new mensaje", async (event) => {
			const mensaje = JSON.parse(event.data);
			this.urlSearchparamsObj.id_mensaje = mensaje;

			await this.fetchWithoutForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', this.urlSearchparamsObj);
		});
	}

	detectarEnlaces(texto) {
		const urlRegex = /(https?:\/\/[^\s]+)/g;
		return texto.replace(urlRegex, url => `<a href="${url}" target="_blank">${url}</a>`);
	}

	detectarEnlacesAvanzado(texto) {
		const urlRegex = /((https?:\/\/|www\.)[^\s]+|[a-z0-9.-]+\.[a-z]{2,}(\/[^\s]*)?)/gi;

		return texto.replace(urlRegex, url => {
			let enlace = url;

			// Si no tiene protocolo, se lo añadimos
			if (!/^https?:\/\//i.test(url)) {
				enlace = "https://" + url;
			}

			return `<a href="${enlace}" target="_blank" rel="noopener noreferrer">${url}</a>`;
		});
	}

	// MARK: MENSAJES TEMPLATE

	mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje => {

			let marcador = "";

			if (!this.mostrado && mensaje.id_mensaje > this.ultimoIdLeido) {
				marcador = "<p id='marcador'>Nuevos mensajes</p>";
				this.mostrado = true; // ← ya no se vuelve a mostrar
			}

			const fechaEnvio = this.hoursMinutes(mensaje.fecha_envio);
			const fechaMensajeActual = this.formatearFecha(mensaje.fecha_envio);
			const fechaMensajeAnterior = this.mensaje.fecha;

			const cambioFecha = fechaMensajeAnterior?.dayOfYear === fechaMensajeActual.dayOfYear
				? ""
				: `<date datetime="${this.yearMonthDay(mensaje.fecha_envio)}">${this.compareTime(mensaje.fecha_envio)}</date>`;

			const nombreAutor = mensaje.nombre_usuario === this.mensaje?.autor
				? ""
				: `<h2 translate="no">${mensaje.nombre_usuario}</h2>`;

			const isAutor = mensaje.id_emisor === this.usuario.id_usuario;

			const classArticle = isAutor
				? 'class="mensaje-propio"'
				: '';

			const formDelete = isAutor
				? `<form method="POST" name="deleteMensaje">
						<input type="hidden" name="id_mensaje" value="${mensaje.id_mensaje}">
						<button>
							<svg viewBox="0 0 928 983">
								<path d="M880.09 95.543H681.62l-3.2-43.688C676.31 23.079 652.35.81 623.5.81H303.82c-28.85 0-52.81 22.27-54.92 51.045l-3.2 43.688H47.23c-26.06 0-47.17 21.12-47.17 47.172s21.11 47.172 47.17 47.172h832.86c26.06 0 47.18-21.12 47.18-47.172s-21.12-47.172-47.18-47.172zM54.64 225.899l49.25 672.171c3.51 47.98 43.47 85.12 91.58 85.12h536.38c48.12 0 88.07-37.14 91.58-85.12l49.25-672.171H54.64zm241.1 601.221c-.44.02-.87.04-1.3.04a20.31 20.31 0 0 1-20.24-19.01l-26.83-421.639c-.71-11.182 7.78-20.831 18.97-21.544 11.17-.705 20.82 7.784 21.54 18.966l26.83 421.637c.7 11.19-7.79 20.83-18.97 21.55zm188.21-20.25c0 11.2-9.09 20.29-20.29 20.29s-20.29-9.09-20.29-20.29V385.218c0-11.207 9.09-20.284 20.29-20.284s20.29 9.077 20.29 20.284V806.87zm196-420.359L653.11 808.15c-.68 10.74-9.6 19.01-20.22 19.01-.44 0-.87-.02-1.31-.04a20.31 20.31 0 0 1-18.96-21.55l26.83-421.637c.71-11.182 10.37-19.67 21.54-18.966 11.18.713 19.67 10.362 18.96 21.544z"/>
							</svg>
						</button>
					</form>`
				: '';

			const template =
				`
					${cambioFecha}
					${marcador}
					<article ${classArticle}>
						${nombreAutor}
						<div>
							<p>${this.detectarEnlacesAvanzado(mensaje.contenido)}</p>
							<date>${fechaEnvio}</date>
						</div>
						${formDelete}
					</article>
				`;

			this.mensaje.autor = mensaje.nombre_usuario;
			this.mensaje.fecha = this.formatearFecha(mensaje.fecha_envio);

			return template;
		});

		return mensajes.join("");
	}

	// MARK: CREATE MENSAJES

	async createMensaje(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	// MARK: DELETE MENSAJES

	async deleteMensaje(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.ELIMINAR);
		if (response.status === 204) {
			form.closest("article").remove();
		}
	}

	// MARK: WRITE CHAT

	writeChat() {

		if (this.urlSearchParams.size === 2) {
			this.dom.h1.textContent = this.urlSearchParams.get('nombre');

			if (this.urlSearchParams.has('id_grupo')) {
				const formInvitar =
					`
						<form method="POST" name="invitarGrupo">
							<input type="hidden" value="${this.urlSearchParams.get('id_grupo')}" name="id_grupo">
							<select name="id_contacto" id="id_contacto" required>
								<option value="">Añadir a...</option>
							</select>
							<button>Invitar</button>
						</form>
					`;

				this.dom.header.insertAdjacentHTML('beforeend', this.sanitize(formInvitar));

				this.streamContactosInvitables(this.urlSearchParams.get('id_grupo'));
			}
		}
	}
}