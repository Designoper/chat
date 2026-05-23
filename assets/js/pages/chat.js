import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

await conexion.sessionCheck();
conexion.writeChat();
conexion.delete();
conexion.setForm();
conexion.setObj();
await conexion.getUltimoId();
await conexion.getMensajes();

conexion.scrollToCurrent();

conexion.streamMensajes();
conexion.streamConexion();