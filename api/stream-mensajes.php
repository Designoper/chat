<?php

set_time_limit(0);

// ignore_user_abort(true);

define('NO_SESSION', true);

session_start();
$id_usuario = (int) $_SESSION['id_usuario'] ?? null;
session_write_close();

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id = (int) $_GET["ultimo_id"] ?? 0;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

while (true) {

    $mensaje = new Mensaje();
    $mensajes = $mensaje->getNuevosMensajesPublicos($ultimo_id);

    if (!empty($mensajes)) {

        foreach ($mensajes as $m) {
            $ultimo_id = $m["id_mensaje"];

            echo "event: mensaje\n";
            echo "data: " . json_encode($m) . "\n\n";
        }

        echo "event: new mensaje\n";
        echo "data: " . json_encode($ultimo_id) . "\n\n";
    }

    // echo "event: ping\n";
    // echo "data: 1\n\n"; // ← OBLIGATORIO

    ob_flush();
    flush();

    sleep(1);
}
