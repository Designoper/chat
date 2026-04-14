export class Fetch {
	constructor() { }

	async simpleFetch(url) {
		const response = await fetch(url);
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