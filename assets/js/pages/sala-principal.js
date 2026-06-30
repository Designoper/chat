import Contacto from '../modules/Contacto.js';

const contacto = new Contacto();

await contacto.sessionCheck();
await contacto.streamContactos();
await contacto.streamInvitaciones();
contacto.imprimirCodigo();