export class Fetch {
	constructor() { }

	async simpleFetch(url) {
		const response = await fetch(url);
		const json = await response.json();
		return json;
	}

	async fetchData(form) {

		const init = {};
		const userInputs = new FormData(form);
		const sendButton = form.querySelector('button:not([type="reset"], [type="button"])');
		const method = sendButton.value.toUpperCase();
		const url = new URL(form.action);

		const output = form.querySelector('output');
		const dialog = form.closest('dialog');

		switch (method) {
			case 'GET':
				url.search = new URLSearchParams(userInputs);
				break;

			case 'POST':
			case 'PUT':
				init.method = 'POST';
				init.body = userInputs;
				break;

			case 'DELETE':
				init.method = 'DELETE';
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
				`<p>Errores de validación:</p>

				<ul>
					${response.validationErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`
		}

		if (response.integrityErrors?.length > 0) {
			output.innerHTML =
				`<p>Errores de integridad:</p>

				<ul>
					${response.integrityErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`
		}
	}

	resetForm(form, method, errorContainer, dialog) {
		form && method !== "GET" ? form.reset() : null;
		dialog ? dialog.close() : null;
		errorContainer ? errorContainer.innerHTML = "" : null;
	}
}