import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

await mensaje.sessionCheck();
await mensaje.writeChat();
mensaje.delete();
mensaje.setForm();
mensaje.setObj();
await mensaje.getUltimoId();
await mensaje.getMensajes();

mensaje.scrollToCurrent();

mensaje.streamMensajes();