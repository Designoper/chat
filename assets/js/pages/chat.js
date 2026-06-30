import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

await mensaje.currentUsuario();
await mensaje.writeChat();
mensaje.delete();
mensaje.setForm();
mensaje.setObj();
await mensaje.getUltimoId();
await mensaje.getMensajes();

mensaje.scrollToCurrent();

mensaje.streamMensajes();