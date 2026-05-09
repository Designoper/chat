import MensajeDirecto from "../modules/MensajeDirecto.js";

const mensajeDirecto = new MensajeDirecto();
await mensajeDirecto.sessionCheck();
await mensajeDirecto.getMensajesDirectos();
mensajeDirecto.writeChat();
mensajeDirecto.streamMensajesDirectos();
mensajeDirecto.formHandler();