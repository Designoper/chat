import Endpoint from "./Endpoint.js";

export default class SanitizerAPI extends Endpoint {
	constructor() {
		super();
	}

	sanitizeDefault(dom, content) {
		dom.setHTML(content, {
			sanitizer: new Sanitizer({})
		});
	}

	sanitizeDefault2(dom, content) {
		dom.setHTML(content, {
			sanitizer: new Sanitizer({
				comments: false,
				attributes: false
			})
		});
	}

	sanitizeDefault3() {
		{
			sanitizer: new Sanitizer({
				comments: true,
				dataAttributes: true,

			});
		}
	}

	sanitizeDefault4() {
		{
			sanitizer: new Sanitizer({

			}).setComments(true);
		}
	}

	sanitizeHTML(html) {
		html, {
			sanitizer: new Sanitizer({})
		};
	}
}