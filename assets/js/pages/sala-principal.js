import Usuario from "../modules/Usuario.js";

const usuario = new Usuario();

await usuario.getUsuarios();
await usuario.getMensajesNoLeidos();