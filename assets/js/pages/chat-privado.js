import Conexion from '../modules/Conexion.js';

const conexion = new Conexion();

// const getParams = {
// 	"id_receptor": conexion.id_receptor
// }

await conexion.sessionCheck();
conexion.setUrlStream(conexion.urlStreamMensajes)

conexion.setForm();
await conexion.getMensajes();


// conexion.test();

// conexion.setData(data, conexion.mensajeData, conexion.urlStreamMensajes);

// await conexion.fetchWithoutForm(conexion.ENDPOINTS.POST.MENSAJES.ULTIMO_ID, 'post', conexion.mensajeData);

conexion.writeChat(`${conexion.nombre_receptor}`, conexion.id_receptor);
// console.log(conexion.urlStreamMensajes)
conexion.streamMensajes(conexion.urlStreamMensajes);

conexion.setUrlStream(conexion.urlStreamConexion);

// conexion.setData(getParams, conexion.conexionData, conexion.urlStreamConexion);

conexion.streamConexion(conexion.urlStreamConexion);
