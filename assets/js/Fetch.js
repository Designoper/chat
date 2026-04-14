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

		init.method = method;
		const output = form.querySelector('output');

		switch (method) {
			case 'GET':
				url.search = new URLSearchParams(userInputs);
				break;

			case 'POST':
				init.body = userInputs;
		}

		try {
			const response = await fetch(url, init);

			if (response.status === 204) {
				return response;
			}

			const json = await response.json();
			json.status = response.status;

			response.ok
				? null
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
}