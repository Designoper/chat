import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

mensaje.setEndpoints();
await mensaje.currentUsuario();
mensaje.writeChat();
mensaje.delete();
mensaje.setForm();
mensaje.setObj();
mensaje.sendFileOnInput();
await mensaje.getUltimoUlid();
mensaje.streamMensajes();
// mensaje.geolocate();
mensaje.formhelper();
mensaje.geolocate2();