<?php

set_time_limit(0);
ignore_user_abort(true);

define('NO_SESSION', true);

session_start();
$id_usuario = (int) ($_SESSION['id_usuario'] ?? 0);
session_write_close();

require_once __DIR__ . "/../models/Mensaje.php";

$ultimo_id   = (int) ($_GET["ultimo_id"] ?? 0);
$id_receptor = (int) ($_GET["id_receptor"] ?? 0);
$id_grupo    = (int) ($_GET["id_grupo"] ?? 0);
$tipo        = $_GET["tipo"] ?? null;

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

$mensaje = new Mensaje();
$startTime = time();
$lastPing = 0;

while (true) {

    if (connection_aborted()) {
        break;
    }

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

        default:
            echo "event: error\n";
            echo "data: Tipo de stream no válido\n\n";
            flush();
            exit;
    }

    if (!empty($mensajes)) {

        foreach ($mensajes as $m) {
            $ultimo_id = $m["id_mensaje"];

            echo "event: mensaje\n";
            echo "data: " . json_encode($m) . "\n\n";
        }

        echo "event: new mensaje\n";
        echo "data: " . json_encode($ultimo_id) . "\n\n";
    } else {
        // Heartbeat cada 15 segundos
        $elapsed = time() - $startTime;

        if ($elapsed - $lastPing >= 15) {
            echo "event: ping\n";
            echo "data: {}\n\n";

            $lastPing = $elapsed;
        }
    }

    @ob_flush();
    @flush();

    // Evita saturar CPU
    usleep(300000); // 0.3s
}
