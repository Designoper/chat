let ultimoId = 0;

const evtSource = new EventSource(`${location.origin}/api/stream-mensajes.php?ultimo_id=${ultimoId}`);

evtSource.addEventListener("mensaje", (e) => {
    const mensaje = JSON.parse(e.data);
	console.log(mensaje);
    // mostrarMensaje(mensaje);
    ultimoId = mensaje.id_mensaje;
});

evtSource.addEventListener("ping", () => {
    // opcional
	console.log("keepalive");
});
