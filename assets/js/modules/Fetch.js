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

	// MARK: FETCH WITHOUT FORM
	async fetchWithoutForm(endpoint, method, data = {}) {
		const init = {};
		init.method = method;
		const url = new URL(endpoint);
		const userInputs = this.objToFormdata(data);

		switch (method) {
			case 'get':
				url.search = new URLSearchParams(userInputs);
				break;
			case 'post':
				init.body = userInputs;
				break;
		}

		try {
			const response = await fetch(url, init);
			const json = await response.json();

			json.status = response.status;
			return json;
		}

		catch (error) {
			console.log(error);
		}
	}

	// MARK: FETCH DATA
	async fetchData(form, action) {

		const init = {};
		const method = form.method;
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