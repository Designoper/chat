import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

await mensaje.sessionCheck();

const lastid = await mensaje.getMensajes();

const obj = {
	"ultimo_id": lastid,
	"tipo": "publico"
}

await mensaje.fetchPostNoForm(mensaje.ENDPOINTS.ULTIMO_ID_MENSAJE, obj);
mensaje.setData(obj)
mensaje.streamMensajes(mensaje.ENDPOINTS.ULTIMO_ID_MENSAJE);
mensaje.formHandler();