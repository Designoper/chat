import Usuario from "./Usuario.js";

export default class Mensaje extends Usuario {

	urlStreamMensajes = new URL(this.ENDPOINTS.GET.MENSAJES.STREAM);
	endpointMensaje = this.ENDPOINTS.GET.MENSAJES.TODOS;

	params = new URLSearchParams(location.search);
	ringtone = new Audio("../../../assets/audio/ringtone.mp3");

	mostrado = false;

	dom = {
		header: document.querySelector('header'),
		h1: document.querySelector('h1'),
		output: document.querySelector('output'),
		form: document.querySelector('form'),
		a: document.querySelector('a'),
	};

	mensaje = {};

	constructor() {
		super();
	}

	async getUltimoId() {
		const response = await this.fetchWithoutForm(this.ENDPOINTS.GET.MENSAJES.ULTIMO_ID, 'get', this.paramsObj);
		this.prueba = response.content.id_mensaje;
	}

	delete() {
		for (const key of this.params.keys()) {
			if (!key.startsWith("id")) {
				this.params.delete(key);
			}
		}
	}

	setObj() {
		this.paramsObj = Object.fromEntries(this.params);
	}

	setForm() {
		for (const [key, value] of this.params.entries()) {
			this.dom.form.insertAdjacentHTML('afterbegin', `<input type="hidden" name="${key}" value="${value}">`);
		}
	}

	scrollToCurrent() {
		const marcador = this.dom.output.querySelector("#marcador");

		marcador
			? marcador.scrollIntoView({
				behavior: "smooth",
				block: "center"
			})
			: globalThis.scrollTo({
				top: document.body.scrollHeight,
				behavior: "smooth"
			});
	}

	async getMensajes() {
		const response = await this.fetchWithoutForm(this.endpointMensaje, 'get', this.paramsObj);
		const mensajes = this.mensajesTemplate(response.content);
		this.dom.output.innerHTML = mensajes;

		if (response.content.length > 0) {
			this.paramsObj.ultimo_id = response.content[response.content.length - 1].id_mensaje;
			await this.fetchWithoutForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', this.paramsObj);
		}
	}

	streamMensajes() {

		this.urlStreamMensajes.search = this.params;
		const evtSource = new EventSource(this.urlStreamMensajes);
		this.mostrado = true;

		evtSource.addEventListener("mensaje", (event) => {
			const mensaje = JSON.parse(event.data);
			const content = this.mensajesTemplate([mensaje]);

			this.dom.output.insertAdjacentHTML("beforeend", content);
			this.ringtone.play();
		});

		evtSource.addEventListener("new mensaje", async (event) => {
			const mensaje = JSON.parse(event.data);
			this.paramsObj.ultimo_id = mensaje;

			await this.fetchWithoutForm(this.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', this.paramsObj);
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

	mensajesTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje => {

			let etiqueta = "";

			if (!this.mostrado && mensaje.id_mensaje > this.prueba) {
				etiqueta = "<p id='marcador'>Nuevos mensajes</p>";
				this.mostrado = true; // ← ya no se vuelve a mostrar
			}

			const fechaFinal = this.hoursMinutes(mensaje.fecha_envio);
			const fechaMensajeActual = this.formatearFecha(mensaje.fecha_envio);
			const fechaMensajeAnterior = this.mensaje.fecha;

			let fechatest;

			if (fechaMensajeAnterior?.day === fechaMensajeActual.day &&
				fechaMensajeAnterior?.month === fechaMensajeActual.month &&
				fechaMensajeAnterior?.year === fechaMensajeActual.year) {
				fechatest = "";
			}

			else {
				fechatest = `<p class="cambio-dia">${this.yearMonthDayWeekday(mensaje.fecha_envio)}</p>`;
			}

			let autor = `<p translate="no" class="autor">${mensaje.nombre_usuario}</p>`;

			if (mensaje.nombre_usuario === this.mensaje?.autor) {
				autor = "";
			}

			const template = `
			${fechatest}
            ${etiqueta}
			<article ${mensaje.id_emisor === this.id_usuario ? 'class="mensaje-propio"' : ''}>
				${autor}
				<div>
					<p class="contenido">${this.detectarEnlacesAvanzado(mensaje.contenido)}</p>
					<p class="fecha">${fechaFinal}</p>
				</div>
				${mensaje.id_emisor === this.id_usuario
					? `<form method="POST" name="deleteMensaje">
						<input type="hidden" name="id_mensaje" value="${mensaje.id_mensaje}">
						<button>
							<svg viewBox="0 0 928 983">
								<path d="M880.09 95.543H681.62l-3.2-43.688C676.31 23.079 652.35.81 623.5.81H303.82c-28.85 0-52.81 22.27-54.92 51.045l-3.2 43.688H47.23c-26.06 0-47.17 21.12-47.17 47.172s21.11 47.172 47.17 47.172h832.86c26.06 0 47.18-21.12 47.18-47.172s-21.12-47.172-47.18-47.172zM54.64 225.899l49.25 672.171c3.51 47.98 43.47 85.12 91.58 85.12h536.38c48.12 0 88.07-37.14 91.58-85.12l49.25-672.171H54.64zm241.1 601.221c-.44.02-.87.04-1.3.04a20.31 20.31 0 0 1-20.24-19.01l-26.83-421.639c-.71-11.182 7.78-20.831 18.97-21.544 11.17-.705 20.82 7.784 21.54 18.966l26.83 421.637c.7 11.19-7.79 20.83-18.97 21.55zm188.21-20.25c0 11.2-9.09 20.29-20.29 20.29s-20.29-9.09-20.29-20.29V385.218c0-11.207 9.09-20.284 20.29-20.284s20.29 9.077 20.29 20.284V806.87zm196-420.359L653.11 808.15c-.68 10.74-9.6 19.01-20.22 19.01-.44 0-.87-.02-1.31-.04a20.31 20.31 0 0 1-18.96-21.55l26.83-421.637c.71-11.182 10.37-19.67 21.54-18.966 11.18.713 19.67 10.362 18.96 21.544z"/>
							</svg>
						</button>
					</form>`
					: ''
				}
			</article>
			`;

			this.mensaje.autor = mensaje.nombre_usuario;
			this.mensaje.fecha = this.formatearFecha(mensaje.fecha_envio);

			return template;
		});

		return mensajes.join("");
	}

	async createMensaje(form) {
		await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.CREAR);
	}

	async deleteMensaje(form) {
		const response = await this.fetchData(form, this.ENDPOINTS.POST.MENSAJES.ELIMINAR);
		if (response.status === 204) {
			form.closest("article").remove();
		}
	}

	writeChat() {
		if (this.params.size === 2) {
			// this.params.forEach((value, key) => {
			if (this.params.has('nombre_receptor')) {
				this.dom.h1.insertAdjacentHTML("afterbegin", this.params.get('nombre_receptor'));
				this.dom.header.insertAdjacentHTML('beforeend',
					`<svg viewBox="0 0 100 100">
						<circle cx="50" cy="50" r="50" />
					</svg>`);
				return;
			}
			// });
		}

		if (this.params.size === 2) {
			this.params.forEach((value, key) => {
				if (key.startsWith('nombre_grupo')) {
					this.dom.h1.insertAdjacentHTML("afterbegin", `${value}`);
					return;
				}
			});
		}

		if (this.params.size === 0) {
			this.dom.h1.insertAdjacentHTML("afterbegin", "Chat público");
		}
	}
}