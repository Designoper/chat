import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

await conexion.sessionCheck();
conexion.setUrlStream(conexion.urlStreamMensajes)

conexion.setForm();
await conexion.getMensajes();

conexion.writeChat(conexion.nombre_receptor);

conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.setUrlStream(conexion.urlStreamConexion);

conexion.streamConexion(conexion.urlStreamConexion);
