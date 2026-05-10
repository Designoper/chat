<?php

set_time_limit(0);

// ignore_user_abort(true);

define('NO_SESSION', true);

session_start();
$id_usuario = (int) $_SESSION['id_usuario'] ?? null;
session_write_close();

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id = (int) $_GET["ultimo_id"] ?? 0;

$id_receptor = $_GET["id_receptor"] ?? null;

if ($id_receptor) {
    $id_receptor = (int) $id_receptor;
}

$id_grupo = $_GET["id_grupo"] ?? null;

if ($id_grupo) {
    $id_grupo = (int) $id_grupo;
}

$tipo = $_GET["tipo"] ?? null;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

while (true) {

    $mensaje = new Mensaje();

    switch ($tipo) {
        case "publico":
            $mensajes = $mensaje->getNuevosMensajesPublicos($ultimo_id);
            break;
        case "directo":
            $mensajes = $mensaje->getNuevosMensajesDirectos($ultimo_id, $id_usuario, $id_receptor);
            break;
        case "grupal":
            $mensajes = $mensaje->getNuevosMensajesGrupales($ultimo_id, $id_grupo);
            break;
    }

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
