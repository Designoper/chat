import { Fetch } from "./Fetch.js";

export class Categoria extends Fetch {
	static ENDPOINT =  `${location.protocol}//${location.host}/api/categorias`;
	static categorias;

	constructor() {
		super();
	}

	async getCategorias() {
		const response = await this.simpleFetch(Categoria.ENDPOINT);
		Categoria.categorias = response.content;
	}

	categoriasTemplate(fetchedCategorias) {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria.id}'>
				${categoria.categoria}
			</option>`
		).join('');

		return categorias;
	}

	printCategorias(place) {
		const content = this.categoriasTemplate(Categoria.categorias);
		place.outerHTML = content;
	}
}
