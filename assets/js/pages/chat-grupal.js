import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const endpointMensaje = new URL(mensaje.ENDPOINTS.GET_MENSAJES);
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_GRUPAL;

const endpointParams = new URLSearchParams();
endpointParams.append("id_grupo", mensaje.id_grupo);
endpointMensaje.search = endpointParams;

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(endpointMensaje);

const obj = {
	"ultimo_id": lastid,
	"id_grupo": mensaje.id_grupo,
	"tipo": "grupal"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj);
mensaje.writeChat(mensaje.nombre_grupo, mensaje.id_grupo);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();