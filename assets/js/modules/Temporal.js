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
			year: "numeric",
			month: "numeric",
			day: "numeric",
			hour: "numeric",
			minute: "numeric"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);

		return temporal;
	}

	minutesAndSeconds(date) {
		const options = {
			hour: "numeric",
			minute: "numeric"
		};

		const temporal = this.formatearFecha(date).toLocaleString(undefined, options);
		return temporal;
	}
}