import Usuario from "../modules/Usuario.js";
import Grupo from '../modules/Grupo.js';

const usuario = new Usuario();

await usuario.sessionCheck();
await usuario.getUsuarios();
await usuario.getMensajesNoLeidos();

const grupo = new Grupo();

await grupo.getGruposMiembro();
await grupo.getGruposPendiente();