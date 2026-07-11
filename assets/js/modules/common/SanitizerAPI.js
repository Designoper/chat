import Endpoint from "./Endpoint.js";

export default class SanitizerAPI extends Endpoint {
	constructor() {
		super();
	}

	sanitize(htmlInseguro) {

		const contenedorTemporal = document.createElement('div');
		contenedorTemporal.setHTML(htmlInseguro, { sanitizer: new Sanitizer({}) });
		const htmlLimpio = contenedorTemporal.innerHTML;

		return htmlLimpio;
	}
}