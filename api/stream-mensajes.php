<?php

define('NO_SESSION', true);

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id = $_GET["ultimo_id"] ?? 0;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

while (true) {

    $mensaje = new Mensaje();
    $mensajes = $mensaje->getNuevosMensajesPublicos($ultimo_id);
    // $mensaje->setUltimaConexionPublica();

    if (!empty($mensajes)) {
        foreach ($mensajes as $m) {
            $ultimo_id = $m["id_mensaje"];

            echo "event: mensaje\n";
            echo "data: " . json_encode($m) . "\n\n";
        }
        ob_flush();
        flush();
    }

    // echo "event: ping\n";
    // echo "data: keepalive\n\n";
    ob_flush();
    flush();

    sleep(1);
}
