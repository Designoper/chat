import Usuario from "../modules/Usuario.js";
import Grupo from '../modules/Grupo.js';

const grupo = new Grupo();

await grupo.sessionCheck();
await grupo.finalPrint();
// await usuario.getUsuarios();
// await usuario.getMensajesNoLeidos();


// await grupo.getGruposMiembro();
// await grupo.getGruposPendiente();