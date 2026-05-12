import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const getParams = {
	"id_grupo": mensaje.id_grupo
}

await mensaje.sessionCheck();

await mensaje.getMensajes(getParams);

await mensaje.fetchPostNoForm(mensaje.ENDPOINTS.ULTIMO_ID_MENSAJE, getParams);
mensaje.writeChat(`Chat grupal (${mensaje.nombre_grupo})`, mensaje.id_grupo);
mensaje.streamMensajes();
mensaje.formHandler();