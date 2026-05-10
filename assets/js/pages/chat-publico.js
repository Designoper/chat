import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();
const endpointMensaje = mensaje.ENDPOINTS.GET_MENSAJES;
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_PUBLICO

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(endpointMensaje);

const obj = {
	"ultimo_id": lastid,
	"tipo": "publico"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj)
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();