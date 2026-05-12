import Usuario from "../modules/Usuario.js";

const usuario = new Usuario();

const sesion = await usuario.sessionCheck();
