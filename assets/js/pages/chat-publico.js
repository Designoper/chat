import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

await conexion.sessionCheck();

conexion.setUrlStream(conexion.urlStreamMensajes)

await conexion.getMensajes();

conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.streamConexion(conexion.urlStreamConexion);