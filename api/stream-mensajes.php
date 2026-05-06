<?php
define('NO_SESSION', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_write_close();

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id = $_GET["ultimo_id"] ?? 0;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

while (true) {

    $mensajes = new Mensaje()->getNuevosMensajesPublicos($ultimo_id);

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
