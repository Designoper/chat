import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const endpointMensaje = new URL(mensaje.ENDPOINTS.GET_MENSAJES);
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_DIRECTO;

const endpointParams = new URLSearchParams();
endpointParams.append("id_receptor", mensaje.id_receptor);
endpointMensaje.search = endpointParams;

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(endpointMensaje);

const obj = {
	"ultimo_id": lastid,
	"id_receptor": mensaje.id_receptor,
	"tipo": "directo"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj);
mensaje.writeChat(mensaje.nombre_receptor, mensaje.id_receptor);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();