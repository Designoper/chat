import MensajeGrupal from "../modules/MensajeGrupal";

const mensajeGrupal = new MensajeGrupal();
await mensajeGrupal.sessionCheck();
mensajeGrupal.writeChat();
mensajeGrupal.streamMensajesGrupales();
mensajeGrupal.formHandler();