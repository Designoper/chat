import Mensaje from '../modules/Mensaje.js';

const mensaje = new Mensaje();

const getParams = {
	"id_grupo": mensaje.id_grupo
}

await mensaje.sessionCheck();

await mensaje.getMensajes(getParams);

mensaje.writeChat(`Chat grupal (${mensaje.nombre_grupo})`, mensaje.id_grupo);
mensaje.streamMensajes();