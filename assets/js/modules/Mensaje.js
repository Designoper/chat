import Contacto from "./Contacto.js";

export default class Mensaje extends Contacto {

	endpointGetUltimoUlId;
	endpointSetUltimoUlId;
	endpointGetMensajes;
	endpointStreamMensajes;
	endpointArchivo;
	ulid_type;
	ulid_value;

	urlSearchParams = new URLSearchParams(location.search);
	ringtone = new Audio(`${location.origin}/assets/audio/ringtone.mp3`);

	flags = {
		scroll: true,
		marcador: true
	};

	dom = {
		header: document.querySelector('header'),
		h1: document.querySelector('h1'),
		output: document.querySelector('output'),
		form: document.querySelectorAll('form'),
	};

	mensaje = {};

	ultimoUlidLeido;

	constructor() {
		super();
	}

	sendFileOnInput() {
		const forms = document.querySelectorAll("form");
		forms.forEach(form => {
			const inputs = form.querySelectorAll("input");
			const sendButton = form.querySelector("button");

			inputs.forEach(input => {
				input.oninput = () => sendButton.click();
			});
		});
	}

	setEndpoints() {
		if (this.urlSearchParams.has('ulid_contacto')) {
			this.endpointGetUltimoUlid = this.ENDPOINTS.GET.MENSAJES.ULTIMO_ULID_DIRECTO;
			this.endpointSetUltimoUlid = this.ENDPOINTS.POST.MENSAJES.ULTIMO_ULID_DIRECTO;
			this.endpointGetMensajes = this.ENDPOINTS.GET.MENSAJES.DIRECTOS;
			this.endpointStreamMensajes = this.ENDPOINTS.GET.MENSAJES.STREAM_DIRECTOS;
			this.endpointArchivo = this.ENDPOINTS.GET.MENSAJES.ARCHIVOS.DIRECTO;
			this.ulid_type = 'ulid_contacto';
			this.ulid_value = this.urlSearchParams.get('ulid_contacto');
		}

		if (this.urlSearchParams.has('ulid_grupo')) {
			this.endpointGetUltimoUlid = this.ENDPOINTS.GET.MENSAJES.ULTIMO_ULID_GRUPAL;
			this.endpointSetUltimoUlid = this.ENDPOINTS.POST.MENSAJES.ULTIMO_ULID_GRUPAL;
			this.endpointGetMensajes = this.ENDPOINTS.GET.MENSAJES.GRUPALES;
			this.endpointStreamMensajes = this.ENDPOINTS.GET.MENSAJES.STREAM_GRUPALES;
			this.endpointArchivo = this.ENDPOINTS.GET.MENSAJES.ARCHIVOS.GRUPAL;
			this.ulid_type = 'ulid_grupo';
			this.ulid_value = this.urlSearchParams.get('ulid_grupo');
		}
	}

	geolocate() {
		const geo = document.querySelector("geolocation");
		const form = geo.closest('form');
		const sendButton = form.querySelector('button');
		const input = form.querySelector('input[name="contenido"]');

		geo.onlocation = () => {
			if (confirm('¿Enviar ubicación?')) {
				input.value = `https://www.google.com/maps/search/?api=1&query=${geo.position.coords.latitude},${geo.position.coords.longitude}`;
				sendButton.click();
			}
		};
	}

	formhelper() {
		const forms = document.querySelectorAll("form:has(input[type='file'])");
		forms.forEach(form => {
			const openButton = form.querySelector("button");
			const sendButton = form.querySelector("button[type='submit']");
			const inputFile = form.querySelector("input[type='file']");
			openButton.onclick = () => {
				inputFile.showPicker();
			};
			inputFile.oninput = () => {
				sendButton.click();
			};
		});
	}

	// MARK: GET ULTIMO ULID

	async getUltimoUlid() {
		const response = await this.fetchWithoutForm(this.endpointGetUltimoUlid, 'get', this.urlSearchparamsObj);
		this.ultimoUlidLeido = response.json.ulid_mensaje;
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
			this.dom.form.forEach(form => {
				form.insertAdjacentHTML('afterbegin', this.sanitize(hiddenInput));
			});
		}
	}

	// MARK: SCROLL TO CURRENT

	scrollToCurrent() {
		const marcador = this.dom.output.querySelector("#marcador");

		marcador
			? marcador.scrollIntoView({
				behavior: "instant",
				block: "start"
			})
			: globalThis.scrollTo({
				top: document.documentElement.scrollHeight,
				behavior: "instant"
			});
	}

	// MARK: GET MENSAJES

	async getMensajes() {

		const response = await this.fetchWithoutForm(this.endpointGetMensajes, 'get', this.urlSearchparamsObj);

		if (response.json.length > 0) {
			this.urlSearchparamsObj.ulid_mensaje = response.json[response.json.length - 1].ulid_mensaje;
			await this.fetchWithoutForm(this.endpointSetUltimoUlid, 'post', this.urlSearchparamsObj);
		}

		const mensajes = this.mensajesTemplate(response.json);
		this.dom.output.innerHTML = this.sanitize(mensajes);
		this.flags.marcador = false;
	}

	// MARK: STREAM MENSAJES

	streamMensajes() {

		const evtSource = new EventSource(`${this.endpointStreamMensajes}?${this.ulid_type}=${this.ulid_value}`);
		this.mostrado = true;

		evtSource.onopen = async () => {

			await this.getMensajes();
			if (this.flags.scroll) {
				this.scrollToCurrent();
				this.flags.scroll = false;
			}
		};

		evtSource.addEventListener("mensaje", async (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);

			this.dom.output.insertAdjacentHTML("beforeend", this.sanitize(content));
			this.ringtone.play();

			this.urlSearchparamsObj.ulid_mensaje = mensaje.ulid_mensaje;
			await this.fetchWithoutForm(this.endpointSetUltimoUlid, 'post', this.urlSearchparamsObj);
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

		let marcadorPuesto = false;

		const mensajes = fetchedMensajes.map(mensaje => {

			const fechaMensajeActual = this.formatearFecha(mensaje.fecha_creacion);
			const fechaMensajeAnterior = this.mensaje.fecha;

			const cambioFecha = fechaMensajeAnterior?.dayOfYear === fechaMensajeActual.dayOfYear
				? ""
				: `<date>${this.compareTime(mensaje.fecha_creacion)}</date>`;

			let marcador = "";

			if (this.flags.marcador === true && !marcadorPuesto && mensaje.ulid_mensaje > this.ultimoUlidLeido) {
				marcador = "<p id='marcador'>Nuevos mensajes</p>";
				marcadorPuesto = true; // Marcamos que ya se incluyó en el HTML generado
			}

			const isAutor = mensaje.ulid_emisor === this.usuario.ulid_usuario;

			const classArticle = isAutor
				? 'class="mensaje-propio"'
				: '';

			const nombreAutor = mensaje.nombre_usuario === this.mensaje?.autor
				? ""
				: `<h2 translate="no">${mensaje.nombre_usuario}</h2>`;

			const fechaEnvio = this.hoursMinutes(mensaje.fecha_creacion);

			let fileHref;

			switch (mensaje.tipo_mensaje) {
				case 'image':
					fileHref = `<img src="${this.endpointArchivo}?f=${mensaje.contenido}&ulid_mensaje=${mensaje.ulid_mensaje}&${this.ulid_type}=${this.ulid_value}" loading="lazy">
					<date>${fechaEnvio}</date>`;
					break;
				case 'audio':
					fileHref = `<audio src="${this.endpointArchivo}?f=${mensaje.contenido}&ulid_mensaje=${mensaje.ulid_mensaje}&${this.ulid_type}=${this.ulid_value}" loading="lazy" controls></audio>
					<date>${fechaEnvio}</date>`;
					break;
				case 'video':
					fileHref = `<video src="${this.endpointArchivo}?f=${mensaje.contenido}&ulid_mensaje=${mensaje.ulid_mensaje}&${this.ulid_type}=${this.ulid_value}" loading="lazy" controls></video>
					<date>${fechaEnvio}</date>`;
					break;
				case 'text':
					fileHref =
						`<div>
							<p>${this.detectarEnlacesAvanzado(mensaje.contenido)}</p>
							<date>${fechaEnvio}</date>
						</div>`;
			}

			const formDelete = isAutor
				? `<form method="POST" name="deleteMensaje">
						<input type="hidden" name="ulid_mensaje" value="${mensaje.ulid_mensaje}">
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
						${fileHref}
						${formDelete}
					</article>
				`;

			this.mensaje.autor = mensaje.nombre_usuario;
			this.mensaje.fecha = this.formatearFecha(mensaje.fecha_creacion);

			return template;
		});

		return mensajes.join("");
	}

	// MARK: CREATE MENSAJES

	async createMensajeDirecto(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_DIRECTO);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeDirectoImagen(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_IMAGEN_DIRECTO);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeDirectoAudio(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_AUDIO_DIRECTO);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeDirectoVideo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_VIDEO_DIRECTO);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeGrupal(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_GRUPAL);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeGrupalImagen(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_IMAGEN_GRUPAL);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeGrupalAudio(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_AUDIO_GRUPAL);
		if (response.status === 201) {
			globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		}
	}

	async createMensajeGrupalVideo(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR_VIDEO_GRUPAL);
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

			if (this.urlSearchParams.has('ulid_grupo')) {

				const formInvitar =
					`
						<form method="POST" name="invitarGrupo">
							<input type="hidden" value="${this.ulid_value}" name="ulid_grupo">
							<select name="ulid_contacto" id="ulid_contacto" required>
								<option value="">Añadir a...</option>
							</select>
							<button>Invitar</button>
						</form>
					`;

				this.dom.header.insertAdjacentHTML('beforeend', this.sanitize(formInvitar));

				this.streamContactosInvitables(this.ulid_value);

				// this.dom.form[0].name = 'createMensajeGrupal';
				this.dom.form[0].name = 'createMensajeGrupalImagen';
				this.dom.form[1].name = 'createMensajeGrupalImagen';
				this.dom.form[2].name = 'createMensajeGrupalAudio';
				this.dom.form[3].name = 'createMensajeGrupalAudio';
				this.dom.form[4].name = 'createMensajeGrupalVideo';
				this.dom.form[5].name = 'createMensajeGrupalVideo';
				this.dom.form[6].name = 'createMensajeGrupal';
			}

			if (this.urlSearchParams.has('ulid_contacto')) {

				// this.dom.form[0].name = 'createMensajeDirecto';
				this.dom.form[0].name = 'createMensajeDirectoImagen';
				this.dom.form[1].name = 'createMensajeDirectoImagen';
				this.dom.form[2].name = 'createMensajeDirectoAudio';
				this.dom.form[3].name = 'createMensajeDirectoAudio';
				this.dom.form[4].name = 'createMensajeDirectoVideo';
				this.dom.form[5].name = 'createMensajeDirectoVideo';
				this.dom.form[6].name = 'createMensajeDirecto';
			}
		}
	}
}