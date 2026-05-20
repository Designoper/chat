import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

await conexion.sessionCheck();

await conexion.getMensajes();

// conexion.setData(data, conexion.mensajeData, conexion.urlStreamMensajes);

// await conexion.fetchWithoutForm(conexion.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', conexion.mensajeData);

conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.streamConexion(conexion.urlStreamConexion);