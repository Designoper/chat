const outputElem = document.querySelector("#output");

if (typeof HTMLGeolocationElement === "function") {
	const geo = document.querySelector("geolocation");
	const form = geo.closest('form');
	const button = form.querySelector('button');
	const input = form.querySelector('input[name="contenido"]');
	geo.addEventListener("location", () => {
		if (geo.position) {
			// console.log(`https://www.google.com/maps/place/${geo.position.coords.latitude},${geo.position.coords.longitude}`);
			// console.log(`https://www.google.com/maps/search/?api=1&query=${geo.position.coords.latitude},${geo.position.coords.longitude}`);
			console.log(`${geo.position.coords.latitude},${geo.position.coords.longitude}`);
			if (confirm('¿Enviar ubicación?')) {
				input.value = `https://www.google.com/maps/search/?api=1&query=${geo.position.coords.latitude},${geo.position.coords.longitude}`;
				button.click();
			}
		}
	});
}

// else {
// 	const fallback = document.querySelector("#fallback");
// 	fallback.addEventListener("click", () => {
// 		navigator.geolocation.getCurrentPosition(
// 			(position) => {
// 				outputElem.textContent += `(${position.coords.latitude}, ${position.coords.longitude}), `;
// 			},
// 			(error) => {
// 				outputElem.textContent += `${error.message}, `;
// 			},
// 		);
// 	});
// }