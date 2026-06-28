import Contacto from '../modules/Contacto.js';
import Grupo from '../modules/Grupo.js';
import Usuario from '../modules/Usuario.js';

// const contacto = new Contacto();
const grupo = new Grupo();
// const usuario = new Usuario();

await grupo.sessionCheck();
await grupo.streamContactos();
await grupo.streamGruposPendientes();
await grupo.streamUsuarios();