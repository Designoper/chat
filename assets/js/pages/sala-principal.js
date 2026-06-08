import Grupo from '../modules/Grupo.js';

const grupo = new Grupo();

await grupo.sessionCheck();
await grupo.finalPrint();
await grupo.streamContactos();