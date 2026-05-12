import Usuario from "../modules/Usuario.js";

const usuario = new Usuario();

const sesion = await usuario.sessionCheck();

const peer = new Peer(usuario.id_usuario);

peer.on("open", id => {
	const p = document.querySelector("main p");
	p.innerHTML = `Mi id: ${id}`;
});

navigator.mediaDevices.getUserMedia({ video: true, audio: true })
	.then(stream => {
		document.getElementById("yo").srcObject = stream;

		document.getElementById("llamar").onclick = () => {
			const id = document.getElementById("peerId").value;
			const call = peer.call(id, stream);

			call.on("stream", remote => {
				document.getElementById("otro").srcObject = remote;
			});
		};

		peer.on("call", call => {
			// if (!listaDeIDsPermitidos.includes(call.peer)) return;
			if (confirm("¿Aceptar llamada entrante?")) {
				call.answer(stream);
				call.on("stream", remote => {
					document.getElementById("otro").srcObject = remote;
				});
			}
			else call.close();
			// return;
		});
	});
