import Endpoint from "./Endpoint.js";

export default class TemporalAPI extends Endpoint {
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

	compareTime(date, flag = true) {
		const currentTime = Temporal.Now.zonedDateTimeISO();
		const fecha = this.formatearFecha(date);

		const daysDifference = currentTime.dayOfYear - fecha.dayOfYear;
		const yearsDifference = currentTime.year - fecha.year;

		if (yearsDifference === 0) {
			if (daysDifference === 0 && flag === false) {
				return this.hoursMinutes(date);
			}
			if (daysDifference === 0 && flag === true) {
				return 'Hoy';
			}

			if (daysDifference === 1) {
				return `Ayer`;
			}

			if (daysDifference > 1 && daysDifference < 8) {
				return `${daysDifference} días`;
			}

			return this.yearMonthDay(date);
		}

		if (yearsDifference > 0) {
			return this.yearMonthDay(date);
		}
	}
}