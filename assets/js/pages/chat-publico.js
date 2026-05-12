import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

await mensaje.sessionCheck();
await mensaje.getMensajes();

mensaje.streamMensajes();