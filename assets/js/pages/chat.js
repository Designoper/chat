import Conexion from '../modules/Conexion.js';
import Mensaje from '../modules/Mensaje.js';

const conexion = new Mensaje();

await conexion.sessionCheck();
await conexion.writeChat();
conexion.delete();
conexion.setForm();
conexion.setObj();
await conexion.getUltimoId();
await conexion.getMensajes();

conexion.scrollToCurrent();

conexion.streamMensajes();
// conexion.streamConexion();