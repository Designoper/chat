import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const endpointUltimoId = mensaje.ENDPOINTS.ULTIMO_ID_GRUPAL;

const getParams = {
	"id_grupo": mensaje.id_grupo
}

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes(getParams);

const obj = {
	"ultimo_id": lastid,
	"id_grupo": mensaje.id_grupo,
	"tipo": "grupal"
}

await mensaje.fetchPostNoForm(endpointUltimoId, obj);
mensaje.setData(obj);
mensaje.writeChat(`Chat grupal (${mensaje.nombre_grupo})`, mensaje.id_grupo);
mensaje.streamMensajes(endpointUltimoId);
mensaje.formHandler();