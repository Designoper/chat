import MensajeDirecto from "../modules/MensajeDirecto";

const mensajeDirecto = new MensajeDirecto();
await mensajeDirecto.sessionCheck();
mensajeDirecto.writeChat();
mensajeDirecto.streamMensajesDirectos();
mensajeDirecto.formHandler();