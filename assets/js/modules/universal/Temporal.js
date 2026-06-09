import Endpoint from "./Endpoint.js";

export default class TemporalFormat extends Endpoint {
	constructor() {
		super();
	}

	formatearFecha(date) {

		const instant = Temporal.Instant.from(date);
		const localTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		const fullDate = instant.toZonedDateTimeISO(localTimeZone);

		return fullDate;
	}

	fullDate(date) {
		const options = {
			weekday: "short",
			day: "2-digit",
			month: "2-digit",
			year: "2-digit",
			hour: "2-digit",
			minute: "2-digit"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);

		return temporal;
	}

	yearMonthDay(date) {
		const options = {
			day: "2-digit",
			month: "2-digit",
			year: "2-digit"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);

		return temporal;
	}

	yearMonthDayDash(date) {
		const fecha = this.formatearFecha(date);

		const dia = String(fecha.day).padStart(2, "0");
		const mes = String(fecha.month).padStart(2, "0");
		const año = String(fecha.year).slice(-2);

		return `${dia}-${mes}-${año}`;
	}

	yearMonthDayWeekday(date) {
		const options = {
			weekday: "long",
			day: "2-digit",
			month: "2-digit",
			year: "2-digit"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);

		return temporal;
	}

	hoursMinutes(date) {
		const options = {
			hour: "numeric",
			minute: "numeric"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);

		return temporal;
	}

	compareTime(date) {
		const currentTime = Temporal.Now.zonedDateTimeISO();
		// console.log(currentTime);
		const fecha = this.formatearFecha(date);
		// console.log(fecha);

		if (currentTime.year === fecha.year &&
			currentTime.dayOfYear === fecha.dayOfYear) {
			return this.hoursMinutes(date);
		}

		else if (
			(currentTime.year === fecha.year &&
				currentTime.dayOfYear === fecha.dayOfYear + 1) ||
			(currentTime.year === fecha.year - 1 &&
				currentTime.dayOfYear === 1 && (fecha.dayOfYear === 365 || fecha.dayOfYear === 366)
			)
		) {
			return 'Ayer';
		}

		else return this.yearMonthDay(date);

		// if (currentTime.year === fecha.year &&
		// 	currentTime.dayOfYear - fecha.dayOfYear > 1) {
		// 	return 'Ayer';
		// }

		// if (currentTime.year === fecha.year &&
		// 	currentTime.dayOfYear - fecha.dayOfYear > 1) {
		// 	return 'Ayer';
		// }
	}
}