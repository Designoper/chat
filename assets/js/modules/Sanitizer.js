import Endpoint from "./Endpoint.js";

export default class SanitizerAPI extends Endpoint {
	constructor() {
		super();
	}

	sanitizeString(dom, input) {
		const options = new Sanitizer({});

		dom.setHTML(input, { sanitizer: options });
	}
}