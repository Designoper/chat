import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const endpointMensaje = mensaje.ENDPOINTS.GET_MENSAJES;
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_DIRECTO;

const getParams = {
	"id_receptor": mensaje.id_receptor
}

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(endpointMensaje, getParams);

const obj = {
	"ultimo_id": lastid,
	"id_receptor": mensaje.id_receptor,
	"tipo": "directo"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj);
mensaje.writeChat(`Chat privado con ${mensaje.nombre_receptor}`, mensaje.id_receptor);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();