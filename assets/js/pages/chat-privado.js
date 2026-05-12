import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const getParams = {
	"id_receptor": mensaje.id_receptor
}

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(getParams);

const obj = {
	"ultimo_id": lastid,
	"id_receptor": mensaje.id_receptor,
	"tipo": "directo"
}

await mensaje.fetchPostNoForm(mensaje.ENDPOINTS.ULTIMO_ID_MENSAJE, obj);
mensaje.setData(obj);
mensaje.writeChat(`Chat privado con ${mensaje.nombre_receptor}`, mensaje.id_receptor);
mensaje.streamMensajes();
mensaje.formHandler();