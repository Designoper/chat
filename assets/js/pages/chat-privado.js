import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();
const endpointMensaje = mensaje.ENDPOINTS.GET_MENSAJES_DIRECTOS;
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_DIRECTO

await mensaje.sessionCheck();

const idReceptor = mensaje.getIdReceptor();
const nombreReceptor = mensaje.getNombreReceptor();
const lastid = await mensaje.getMensajes(`${endpointMensaje}?id_receptor=${idReceptor}`);

const obj = {
	"ultimo_id": lastid,
	"id_receptor": idReceptor,
	"tipo": "directo"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj)
mensaje.writeChat(nombreReceptor, idReceptor);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();