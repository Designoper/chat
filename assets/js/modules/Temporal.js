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
		const temporal = this.formatearFecha(date).toLocaleString(undefined,
			{
				weekday: "long",
				year: "numeric",
				month: "numeric",
				day: "numeric",
				hour: "numeric",
				minute: "numeric"
			});

		return temporal;
	}

	minutesAndSeconds(date) {
		const temporal = this.formatearFecha(date);
		temporal.toLocaleString(undefined,
			{
				minute: "numeric",
				seconds: "numeric"
			});

		return temporal;
	}
}