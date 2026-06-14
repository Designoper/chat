import Contacto from '../modules/Contacto.js';
import Grupo from '../modules/Grupo.js';

const contacto = new Contacto();
const grupo = new Grupo();

await contacto.sessionCheck();
await contacto.streamContactos();
await grupo.streamGruposPendientes();