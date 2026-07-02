<?php

require_once __DIR__ . '/../models/SQL.php';

$videos = SQL::executeQuery(
	"SELECT id, video_path FROM mensajes WHERE video_status = 'pending'",
	"",
	[],
	SqlReturn::FetchAll
);

foreach ($videos as $video) {

	$input = $_SERVER['DOCUMENT_ROOT'] . $video['video_path'];

	// Salida según el códec elegido
	$output_h265 = $input . '.optimized.mp4';
	$output_vp9  = $input . '.optimized.webm';
	$output_av1  = $input . '.optimized.mkv';

	/*
    |--------------------------------------------------------------------------
    | Opción 1 — H.265 (HEVC)
    | Ahorro 40–60%, buena calidad, compatible con la mayoría de móviles.
    |--------------------------------------------------------------------------
    */
	$cmd_h265 = "ffmpeg -i \"$input\" -vcodec libx265 -crf 28 -preset medium -acodec aac \"$output_h265\" -y";

	/*
    |--------------------------------------------------------------------------
    | Opción 2 — VP9 (WebM)
    | Ahorro 30–50%, ideal para web, muy buena calidad.
    |--------------------------------------------------------------------------
    */
	$cmd_vp9 = "ffmpeg -i \"$input\" -c:v libvpx-vp9 -b:v 0 -crf 32 -c:a libopus \"$output_vp9\" -y";

	/*
    |--------------------------------------------------------------------------
    | Opción 3 — AV1 (AOM)
    | Ahorro 50–80%, compresión extrema, pero lento.
    |--------------------------------------------------------------------------
    */
	$cmd_av1 = "ffmpeg -i \"$input\" -c:v libaom-av1 -crf 35 -b:v 0 -c:a libopus \"$output_av1\" -y";


	/*
    |--------------------------------------------------------------------------
    | Ejecutar la opción que quieras
    |--------------------------------------------------------------------------
    */

	// HEVC (recomendado)
	exec($cmd_h265);
	$output = $output_h265;

	// VP9
	// exec($cmd_vp9);
	// $output = $output_vp9;

	// AV1
	// exec($cmd_av1);
	// $output = $output_av1;


	/*
    |--------------------------------------------------------------------------
    | Reemplazar el archivo original por el optimizado
    |--------------------------------------------------------------------------
    */
	rename($output, $input);

	/*
    |--------------------------------------------------------------------------
    | Actualizar estado en la base de datos
    |--------------------------------------------------------------------------
    */
	SQL::executeQuery(
		"UPDATE mensajes SET video_status = 'optimized' WHERE id = ?",
		"i",
		[$video['id']]
	);
}
