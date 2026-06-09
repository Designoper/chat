import Contacto from '../modules/Contacto.js';

const contacto = new Contacto();

await contacto.sessionCheck();
await contacto.finalPrint();
await contacto.streamContactos();