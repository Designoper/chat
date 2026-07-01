<?php
$base = __DIR__ . "/private/mensajes/";
$archivo = $base . basename($_GET['f']);

if (!file_exists($archivo)) {
	http_response_code(404);
	exit("Archivo no encontrado");
}

$mime = mime_content_type($archivo);
header("Content-Type: $mime");
readfile($archivo);
