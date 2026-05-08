import Grupo from '../modules/Grupo.js';

const grupo = new Grupo();
await grupo.getGruposMiembro();
await grupo.getGruposPendiente();