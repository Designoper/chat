import Usuario from "./Usuario.js";

export default class TemporalFormat extends Usuario {
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
}