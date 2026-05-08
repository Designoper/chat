import MensajeGrupal from "../modules/MensajeGrupal.js";

const mensajeGrupal = new MensajeGrupal();
await mensajeGrupal.sessionCheck();
mensajeGrupal.writeChat();
mensajeGrupal.streamMensajesGrupales();
mensajeGrupal.formHandler();