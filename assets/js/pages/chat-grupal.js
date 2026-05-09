import MensajeGrupal from "../modules/MensajeGrupal.js";

const mensajeGrupal = new MensajeGrupal();

await mensajeGrupal.sessionCheck();
await mensajeGrupal.getMensajesGrupales();
mensajeGrupal.writeChat();
mensajeGrupal.streamMensajesGrupales();
mensajeGrupal.formHandler();