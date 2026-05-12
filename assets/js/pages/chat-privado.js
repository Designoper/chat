import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const getParams = {
	"id_receptor": mensaje.id_receptor
}

await mensaje.sessionCheck();

await mensaje.getMensajes(getParams);

await mensaje.fetchPostNoForm(mensaje.ENDPOINTS.ULTIMO_ID_MENSAJE, getParams);
mensaje.writeChat(`Chat privado con ${mensaje.nombre_receptor}`, mensaje.id_receptor);
mensaje.streamMensajes();
mensaje.formHandler();