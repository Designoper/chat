<?php

set_time_limit(0);

define('NO_SESSION', true);

session_start();
$id_usuario = (int) $_SESSION['id_usuario'] ?? null;
session_write_close();

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id = (int) $_GET["ultimo_id"] ?? 0;
$id_grupo = (int) $_GET["id_grupo"] ?? null;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

while (true) {

    $mensaje = new Mensaje();
    $mensajes = $mensaje->getNuevosMensajesGrupales($ultimo_id, $id_grupo);

    if (!empty($mensajes)) {
        foreach ($mensajes as $m) {
            $ultimo_id = $m["id_mensaje"];

            echo "event: mensaje\n";
            echo "data: " . json_encode($m) . "\n\n";
        }

        echo "event: new mensaje\n";
        echo "data: " . json_encode($ultimo_id) . "\n\n";
    }

    ob_flush();
    flush();

    sleep(1);
}
