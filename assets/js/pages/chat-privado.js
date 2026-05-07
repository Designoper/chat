import MensajeDirecto from "../modules/MensajeDirecto";

const mensajeDirecto = new MensajeDirecto();
mensajeDirecto.sessionCheck();
mensajeDirecto.writeChat();
mensajeDirecto.streamMensajesDirectos();
mensajeDirecto.formHandler();