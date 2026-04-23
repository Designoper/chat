export default function formatearFecha(date) {

	const instant = Temporal.Instant.from(date);
	const localTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
	const fullDate = instant.toZonedDateTimeISO(localTimeZone);

	return fullDate;
}