import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

const getParams = {
	"id_receptor": conexion.id_receptor
}

const test = {
	"id_receptor": conexion.id_receptor
}

await conexion.sessionCheck();

const data = await conexion.getMensajes(getParams);

conexion.setData(data, conexion.mensajeData, conexion.urlStreamMensajes);

await conexion.fetchWithoutForm(conexion.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', conexion.mensajeData);

conexion.writeChat(`${conexion.nombre_receptor}`, conexion.id_receptor);
conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.setData(test, conexion.conexionData, conexion.urlStreamConexion);

conexion.streamConexion(conexion.urlStreamConexion);
