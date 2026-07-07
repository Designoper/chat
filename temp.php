<?php



// Ejemplo para optimizar un video subido por un usuario
$videoEntrada = 'subidas/video_original.mp4';
$videoSalida = 'web/video_optimizado.webm';

// Comando FFmpeg seguro
$comando = "ffmpeg -i " . escapeshellarg($videoEntrada) . " -vf \"scale='min(1920,iw)':-2\" -c:v libsvtav1 -crf 32 -preset 6 -c:a libopus -b:a 128k " . escapeshellarg($videoSalida) . " 2>&1";

// Ejecutar en el servidor
exec($comando, $output, $resultCode);

if ($resultCode === 0) {
	echo "¡Video optimizado con éxito!";
} else {
	echo "Error al procesar el video.";
}
