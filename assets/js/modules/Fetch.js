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
			const params = data instanceof FormData
				? new URLSearchParams(data)
				: new URLSearchParams(this.objToFormdata(data));

			url.search = params;
		}

		return url;
	}

	async fetchWithoutForm(endpoint, method, data = {}) {
		const upper = method.toUpperCase();
		const init = this.buildRequest(upper, data);
		const url = this.buildURL(endpoint, upper, data);

		try {
			const response = await fetch(url, init);
			const json = await response.json();
			json.status = response.status;
			return json;
		} catch (error) {
			console.error("fetchWithoutForm error:", error);
		}
	}

	async fetchData(form, action) {
		const method = form.method.toUpperCase();
		const data = new FormData(form);

		const init = this.buildRequest(method, data);
		const url = this.buildURL(action, method, data);

		const output = form.querySelector("output");
		const dialog = form.closest("dialog");

		try {
			const response = await fetch(url, init);

			if (response.status === 204) {
				this.resetForm(form, method, output, dialog);
				return response;
			}

			const json = await response.json();
			json.status = response.status;

			response.ok
				? this.resetForm(form, method, output, dialog)
				: this.errorChecker(json, output);

			return json;
		} catch (error) {
			console.error("fetchData error:", error);
		}
	}

	// MARK: ERROR CHECKER

	errorChecker(response, output) {
		if (response.validationErrors?.length > 0) {
			output.innerHTML =
				`<ul>
					${response.validationErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`;
		}

		if (response.integrityErrors?.length > 0) {
			output.innerHTML =
				`<ul>
					${response.integrityErrors.map(error => `<li>${error}</li>`).join("")}
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