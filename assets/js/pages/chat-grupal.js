import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

const getParams = {
	"id_grupo": conexion.id_grupo
}

const test = {
	"id_grupo": conexion.id_grupo
}

await conexion.sessionCheck();

const data = await conexion.getMensajes(getParams);

conexion.setData(data, conexion.mensajeData, conexion.urlStreamMensajes);

await conexion.fetchWithoutForm(conexion.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', conexion.mensajeData);

conexion.writeChat(`Chat grupal (${conexion.nombre_grupo})`, conexion.id_grupo);
conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.setData(test, conexion.conexionData, conexion.urlStreamConexion);

conexion.streamConexion(conexion.urlStreamConexion);
