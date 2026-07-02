import Contacto from "./Contacto.js";

export default class Form extends Contacto {


	constructor() {
		super();
	}

	formTemplate() {
		const form = document.querySelector('section > form');
		const buttonMenu = document.querySelector('section > menu');
		const buttons = buttonMenu.querySelectorAll('button');
		buttons.forEach(button => {
			button.onclick = () => {
				const value = button.value;
				// this.formType(value);

				if (value === 'Texto') {
					form.innerHTML =
						`
							<form method="POST">

								<textarea placeholder="Mensaje" name="contenido" autocomplete="off" minlength="1" maxlength="255" required></textarea>

								<button>
									<svg viewBox="0 0 512 512">
										<path
											d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
									</svg>
								</button>

								<output></output>

							</form>
						`;
				}
			};
		});
	}
}
