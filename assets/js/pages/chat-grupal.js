import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();
const endpointMensaje = mensaje.ENDPOINTS.GET_MENSAJES_GRUPALES;
const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_GRUPAL

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(`${endpointMensaje}?id_grupo=${mensaje.id_grupo}`);

const obj = {
	"ultimo_id": lastid,
	"id_grupo": mensaje.id_grupo,
	"tipo": "grupal"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj)
mensaje.writeChat(mensaje.nombre_grupo, mensaje.id_grupo);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();