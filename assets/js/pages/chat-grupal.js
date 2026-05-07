import MensajeGrupal from "../modules/MensajeGrupal";

const mensajeGrupal = new MensajeGrupal();
mensajeGrupal.sessionCheck();
mensajeGrupal.writeChat();
mensajeGrupal.streamMensajesGrupales();
mensajeGrupal.formHandler();