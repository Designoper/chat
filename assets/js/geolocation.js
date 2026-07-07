const outputElem = document.querySelector("#output");

if (typeof HTMLGeolocationElement === "function") {
	const geo = document.querySelector("geolocation");
	geo.addEventListener("location", () => {
		if (geo.position) {
			// outputElem.textContent += `(${geo.position.coords.latitude},${geo.position.coords.longitude}), `;
			console.log(`(${geo.position.coords.latitude},${geo.position.coords.longitude}), `);
			console.log(`(${geo.position}), `);
			console.log(`(${geo.position.coords}), `);
			console.log(`https://www.google.com/maps/place/${geo.position.coords.latitude},${geo.position.coords.longitude}`);
			console.log(`https://www.google.com/maps/search/?api=1&query=${geo.position.coords.latitude},${geo.position.coords.longitude}`);
		} else if (geo.error) {
			outputElem.textContent += `${geo.error.message}, `;
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