import { Usuario } from "./Usuario.js";

export class MensajeDirecto extends Usuario {
	h1 = document.querySelector('h1');
	input = document.querySelector('input[type="hidden"]');
	id_receptor;
	nombre_receptor;

	constructor() {
		super();
	}

	formatearFecha(date) {

		const instant = Temporal.Instant.from(date);
		const localTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		const fullDate = instant.toZonedDateTimeISO(localTimeZone);

		const Time = {
			year: fullDate.year,
			month: fullDate.month,
			day: fullDate.day,
			dayOfWeek: fullDate.dayOfWeek,
			dayOfYear: fullDate.dayOfYear,
			daysInYear: fullDate.daysInYear,
			hour: fullDate.hour,
			hoursInDay: fullDate.hoursInDay,
			minute: fullDate.minute.toString().padStart(2, "0"),
			second: fullDate.second,
		}

		const dayName = fullDate.toLocaleString(undefined, { weekday: "long" });
		const monthName = fullDate.toLocaleString(undefined, { month: "long" });

		Time.dayOfWeekName = dayName.charAt(0).toUpperCase() + dayName.slice(1);
		Time.monthName = monthName.charAt(0).toUpperCase() + monthName.slice(1);

		// switch (fullDate.dayOfWeek) {
		// 	case 1:
		// 		Time.dayOfWeekName = 'Lunes';
		// 		break;
		// 	case 2:
		// 		Time.dayOfWeekName = 'Martes';
		// 		break;
		// 	case 3:
		// 		Time.dayOfWeekName = 'Miércoles';
		// 		break;
		// 	case 4:
		// 		Time.dayOfWeekName = 'Jueves';
		// 		break;
		// 	case 5:
		// 		Time.dayOfWeekName = 'Viernes';
		// 		break;
		// 	case 6:
		// 		Time.dayOfWeekName = 'Sábado';
		// 		break;
		// 	case 7:
		// 		Time.dayOfWeekName = 'Domingo';
		// }

		// switch (fullDate.month) {
		// 	case 1:
		// 		Time.monthName = 'Enero';
		// 		break;
		// 	case 2:
		// 		Time.monthName = 'Febrero';
		// 		break;
		// 	case 3:
		// 		Time.monthName = 'Marzo';
		// 		break;
		// 	case 4:
		// 		Time.monthName = 'Abril';
		// 		break;
		// 	case 5:
		// 		Time.monthName = 'Mayo';
		// 		break;
		// 	case 6:
		// 		Time.monthName = 'Junio';
		// 		break;
		// 	case 7:
		// 		Time.monthName = 'Julio';
		// 		break;
		// 	case 8:
		// 		Time.monthName = 'Agosto';
		// 		break;
		// 	case 9:
		// 		Time.monthName = 'Septiembre';
		// 		break;
		// 	case 10:
		// 		Time.monthName = 'Octubre';
		// 		break;
		// 	case 11:
		// 		Time.monthName = 'Noviembre';
		// 		break;
		// 	case 12:
		// 		Time.monthName = 'Diciembre';
		// 		break;
		// }

		return Time;
	}

	async initialize() {
		this.getIdReceptor();
		this.sessionCheck();
		this.writeChat();
	}

	getIdReceptor() {
		const currentURL = new URL(window.location.href);
		const idReceptor = currentURL.searchParams.get('id');
		const nombreReceptor = currentURL.searchParams.get('usuario');
		this.id_receptor = idReceptor;
		this.nombre_receptor = nombreReceptor;
	}

	writeChat() {
		this.h1.innerHTML = `Chat privado con ${this.nombre_receptor}`;
		this.input.setAttribute('value', `${this.id_receptor}`);

		setInterval(async () => {
			await this.getMensajesDirectos();
		}, 2000);
	}

	async getMensajesDirectos() {
		const response = await this.simpleFetch(`${this.ENDPOINTS.GET_MENSAJES_DIRECTOS}?id_receptor=${this.id_receptor}`);
		this.printMensajesDirectos(response);
	}

	mensajesDirectosTemplate(fetchedMensajes) {

		const mensajes = fetchedMensajes.map(mensaje =>
			`
			<article ${mensaje.id_emisor == this.user ? 'class="mensaje-propio"' : ''}>
				<p>${mensaje.nombre}</p>
				<p>${mensaje.contenido}</p>
				<p>${this.formatearFecha(mensaje.fecha_creacion).hour}:${this.formatearFecha(mensaje.fecha_creacion).minute}</p>
				${mensaje.id_emisor == this.user
				? `<form name="eliminar-mensaje" action="${this.ENDPOINTS.ELIMINAR_MENSAJES}/${mensaje.id_mensaje}">
						<button>
							<svg viewBox="0 0 928 983">
								<path d="M880.09 95.543H681.62l-3.2-43.688C676.31 23.079 652.35.81 623.5.81H303.82c-28.85 0-52.81 22.27-54.92 51.045l-3.2 43.688H47.23c-26.06 0-47.17 21.12-47.17 47.172s21.11 47.172 47.17 47.172h832.86c26.06 0 47.18-21.12 47.18-47.172s-21.12-47.172-47.18-47.172zM54.64 225.899l49.25 672.171c3.51 47.98 43.47 85.12 91.58 85.12h536.38c48.12 0 88.07-37.14 91.58-85.12l49.25-672.171H54.64zm241.1 601.221c-.44.02-.87.04-1.3.04a20.31 20.31 0 0 1-20.24-19.01l-26.83-421.639c-.71-11.182 7.78-20.831 18.97-21.544 11.17-.705 20.82 7.784 21.54 18.966l26.83 421.637c.7 11.19-7.79 20.83-18.97 21.55zm188.21-20.25c0 11.2-9.09 20.29-20.29 20.29s-20.29-9.09-20.29-20.29V385.218c0-11.207 9.09-20.284 20.29-20.284s20.29 9.077 20.29 20.284V806.87zm196-420.359L653.11 808.15c-.68 10.74-9.6 19.01-20.22 19.01-.44 0-.87-.02-1.31-.04a20.31 20.31 0 0 1-18.96-21.55l26.83-421.637c.71-11.182 10.37-19.67 21.54-18.966 11.18.713 19.67 10.362 18.96 21.544z"/>
							</svg>
						</button>
					</form>`
				: ''}
			</article>
			`
		).join('');

		return mensajes;
	}

	printMensajesDirectos(mensajes) {
		const output = document.querySelector('output');
		const content = this.mensajesDirectosTemplate(mensajes.content);
		output.innerHTML = content;

		this.formHandler();
	}

	async writeMensajeDirecto(form, method, action) {
		await this.fetchData(form, method, action);
	}

	async deleteMensaje(form, method) {
		await this.fetchData(form, method);
	}
}

(async () => {
	await new MensajeDirecto().initialize();
})();
