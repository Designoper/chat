import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

await mensaje.sessionCheck();
mensaje.streamMensajes();
mensaje.formHandler();