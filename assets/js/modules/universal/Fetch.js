export default class Fetch {
	constructor() { }

	// MARK: OBJECT TO FORMDATA
	objToFormdata(obj) {
		const formData = new FormData();

		Object.entries(obj).forEach(([key, value]) => {
			formData.append(key, value);
		});

		return formData;
	}

	buildRequest(method, data) {
		// const request = new Request;
		const init = {};
		init.method = method;

		if (method === "POST") {
			init.body = data instanceof FormData
				? data
				: this.objToFormdata(data);
		}

		return init;
	}

	buildURL(endpoint, method, data) {
		const url = new URL(endpoint);

		if (method === "GET") {
			const urlSearchParams = data instanceof FormData
				? new URLSearchParams(data)
				: new URLSearchParams(this.objToFormdata(data));

			url.search = urlSearchParams;
		}

		return url;
	}

	async fetchWithoutForm(endpoint, method, data = {}) {
		const upper = method.toUpperCase();

		// /** @type {RequestInit} */
		const init = this.buildRequest(upper, data);

		const url = this.buildURL(endpoint, upper, data);

		try {
			const request = new Request(url, init);
			// request.method = method;
			// request.body = data;
			// request.url = endpoint;

			const response = await fetch(request);
			const status = response.status;
			const json = status === 204
				? null
				: await response.json();

			return { status, json };
		} catch (error) {
			console.error("fetchWithoutForm error:", error);
		}
	}

	// /**
	//  * @param {HTMLFormElement} form
	//  * @param {URL} action
	//  */
	async fetchData(form, action) {
		const method = form.method.toUpperCase();
		const data = new FormData(form);

		const init = this.buildRequest(method, data);
		const url = this.buildURL(action, method, data);

		const output = form.querySelector("output");
		const dialog = form.closest("dialog");
		const sendButton = form.querySelector('button');

		sendButton.type = "button";

		try {
			const response = await fetch(url, init);
			const status = response.status;
			const json = status === 204
				? null
				: await response.json();

			response.ok
				? this.resetForm(form, method, output, dialog)
				: this.errorChecker(json, output);

			return { status, json };
		}

		catch (error) {
			console.error("fetchData error:", error);
		}

		finally {
			sendButton.removeAttribute("type");
		}
	}

	async responseToJson(response) {
		const json = await response.json();
		return json;
	}

	// MARK: ERROR CHECKER

	errorChecker(response, output) {
		if (output) {
			output.innerHTML =
				`<ul>
				${response.map(error => `<li>${error}</li>`).join("")}
			</ul>`;
		}
	}

	// MARK: RESET FORM

	resetForm(form, method, output, dialog) {
		form && method !== "get" ? form.reset() : null;
		dialog ? dialog.close() : null;
		output ? output.innerHTML = "" : null;
	}
}