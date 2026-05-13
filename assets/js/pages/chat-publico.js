import Mensaje from '../modules/Mensaje.js';
import Conexion from '../modules/Conexion.js';

const mensaje = new Mensaje();

await mensaje.sessionCheck();
await mensaje.getMensajes();

mensaje.streamMensajes();

const conexion = new Conexion();

conexion.setConexion();