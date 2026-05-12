export default class Fetch {
	constructor() { }

	async simpleFetch(endpoint, params = {}) {
		const url = new URL(endpoint);

		// if (params) {
			const getParams = new URLSearchParams();

			for (const [key, value] of Object.entries(params)) {
				getParams.append(key, value);
			}

			url.search = getParams.toString();
		// }

		const response = await fetch(url);
		const json = await response.json();
		json.status = response.status;
		return json;
	}

	async fetchPostNoForm(endpoint, data) {
		const url = new URL(endpoint);
		const form = new FormData();

		for (const [key, value] of Object.entries(data)) {
			form.append(key, value);
		}

		const response = await fetch(url, {
			method: "POST",
			body: form
		});

		const json = await response.json();
		json.status = response.status;
		return json;
	}

	async fetchData(form, method, action = form.action) {

		const init = {};
		const userInputs = new FormData(form);
		const url = new URL(action);

		const output = form.querySelector('output');
		const dialog = form.closest('dialog');

		init.method = method;

		switch (method) {
			case 'post':
				init.body = userInputs;
				break;

			default:
				url.search = new URLSearchParams(userInputs);
		}

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
		}

		catch (error) {
			console.log(error);
		}
	}

	errorChecker(response, output) {
		if (response.validationErrors?.length > 0) {
			output.innerHTML =
				`<ul>
					${response.validationErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`
		}

		if (response.integrityErrors?.length > 0) {
			output.innerHTML =
				`<ul>
					${response.integrityErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`
		}
	}

	resetForm(form, method, output, dialog) {
		form && method !== "get" ? form.reset() : null;
		dialog ? dialog.close() : null;
		output ? output.innerHTML = "" : null;
	}
}