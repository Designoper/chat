import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

mensaje.setEndpoints();
await mensaje.currentUsuario();
await mensaje.writeChat();
mensaje.delete();
mensaje.setForm();
mensaje.setObj();
await mensaje.getUltimoUlid();
mensaje.streamMensajes();