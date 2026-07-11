import Contacto from '../modules/Contacto.js';

const contacto = new Contacto();

await contacto.currentUsuario();
await contacto.streamContactos();
await contacto.streamInvitaciones();
contacto.imprimirCodigo();