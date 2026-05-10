import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();
const endpointMensaje = mensaje.ENDPOINTS.GET_MENSAJES_DIRECTOS;
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_DIRECTO

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(`${endpointMensaje}?id_receptor=${mensaje.id_receptor}`);

const obj = {
	"ultimo_id": lastid,
	"id_receptor": mensaje.id_receptor,
	"tipo": "directo"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj)
mensaje.writeChat(mensaje.nombre_receptor, mensaje.id_receptor);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();