<?php

declare(strict_types=1);

require_once __DIR__ . '/File.php';

abstract readonly class Setter extends File
{
	protected function __construct()
	{
		parent::__construct();
	}

	// ============================================================================
	// MARK: SET ULID
	// ============================================================================
	protected function setUlid(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$ulid_length = 26;
		$error_message = "El campo $name no puede estar vacío y debe contener $ulid_length carácteres.";

		strlen($value) !== $ulid_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// ============================================================================
	// MARK: SET NOMBRE
	// ============================================================================
	protected function setNombre(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$nombre_max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $nombre_max_length carácteres.";

		empty($value) || strlen($value) > $nombre_max_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// ============================================================================
	// MARK: SET PASSWORD
	// ============================================================================
	protected function setPassword(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$password_max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $password_max_length carácteres.";

		empty($value) || strlen($value) > $password_max_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// ============================================================================
	// MARK: SET CODIGO
	// ============================================================================
	protected function setCodigo(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$codigo_length = 6;
		$error_message = "El campo $name no puede estar vacío y debe contener $codigo_length carácteres.";

		empty($value) || strlen($value) !== $codigo_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// ============================================================================
	// MARK: SET CONTENIDO
	// ============================================================================
	protected function setContenido(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// ============================================================================
	// MARK: SET ARCHIVO
	// ============================================================================
	protected function setArchivo(string $name, FileTypes $tipoEsperado): void
	{
		$filesUploaded = $this->flattenFilesArray($name);

		if (count($filesUploaded) === 0) {
			$this->errors->setValidationError('No has subido ningún archivo.');
			$this->checkValidationErrors();
		}

		if (count($filesUploaded) > 1) {
			$this->errors->setValidationError('Solo se puede subir un archivo.');
			$this->checkValidationErrors();
		}

		$archivo = $filesUploaded[0];

		$tipoDetectado = $this->detectarTipoArchivo($archivo['tmp_name'], $archivo['name']);

		$this->validarArchivoSeguro($archivo['tmp_name'], $tipoDetectado, $tipoEsperado);

		$this->$name = $archivo;
	}

	// ============================================================================
	// MARK: VALIDAR ARCHIVO SEGURO
	// ============================================================================
	private function validarArchivoSeguro(string $ruta, string $tipoDetectado, FileTypes $tipoEsperado): void
	{
		if ($tipoDetectado === null) {
			$this->errors->setValidationError('No se pudo determinar el tipo de archivo.');
			$this->checkValidationErrors();
		}

		if ($tipoDetectado !== strtolower($tipoEsperado->name)) {
			$this->errors->setValidationError("Se esperaba un archivo de tipo $tipoEsperado->name, pero se recibió $tipoDetectado.");
			$this->checkValidationErrors();
		}

		switch ($tipoDetectado) {

			case 'image':
				$info = @getimagesize($ruta);
				if ($info === false) {
					$this->errors->setValidationError('La imagen no es válida o está corrupta.');
					$this->checkValidationErrors();
				}
				break;

			case 'audio':
			case 'video':
				if (filesize($ruta) < 1024) {
					$this->errors->setValidationError('El archivo multimedia es demasiado pequeño para ser válido.');
					$this->checkValidationErrors();
				}
				break;

			default:
				$this->errors->setValidationError('Tipo de archivo no permitido.');
				$this->checkValidationErrors();
		}
	}

	// ============================================================================
	// MARK: DETECTAR TIPO ARCHIVO
	// ============================================================================
	private function detectarTipoArchivo(string $ruta, string $nombreOriginal): ?string
	{
		// --- 0. Extensión ---
		$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

		// Bloqueo inmediato de SVG
		if ($extension === 'svg') {
			$this->errors->setValidationError('Los archivos SVG no están permitidos.');
			$this->checkValidationErrors();
		}

		// Corrección rápida para M4A
		if ($extension === 'm4a') {
			return 'audio';
		}

		// --- 1. Magic numbers ---
		$fh = fopen($ruta, 'rb');
		$bytes = fread($fh, 32);
		fclose($fh);

		$magic = bin2hex($bytes);

		// Imágenes
		if (str_starts_with($magic, 'ffd8ff')) return 'image'; // JPEG
		if (str_starts_with($magic, '89504e47')) return 'image'; // PNG
		if (str_starts_with($magic, '47494638')) return 'image'; // GIF
		if (str_starts_with($magic, '52494646') && strpos($magic, '57454250') !== false) return 'image'; // WebP

		// AVIF (ftypavif / ftypavis)
		if (
			strpos($magic, '6674797061766966') !== false ||
			strpos($magic, '6674797061766973') !== false
		) return 'image';

		// Audio
		if (str_starts_with($magic, '494433')) return 'audio'; // MP3 ID3
		if (str_starts_with($magic, '4f676753')) return 'audio'; // OGG
		if (str_starts_with($magic, '52494646') && strpos($magic, '57415645') !== false) return 'audio'; // WAV

		// MP4 / MOV / M4A
		if (strpos($magic, '667479706d7034') !== false) {
			return 'video';
		}

		// WEBM / MKV (cabecera EBML)
		if (str_starts_with($magic, '1a45dfa3')) {
			return 'video';
		}

		// --- 2. Extensión ---
		$mapExt = [
			'jpg' => 'image',
			'jpeg' => 'image',
			'png' => 'image',
			'gif' => 'image',
			'webp' => 'image',
			'avif' => 'image',

			'mp3' => 'audio',
			'wav' => 'audio',
			'ogg' => 'audio',
			'm4a' => 'audio',

			'mp4' => 'video',
			'mov' => 'video',
			'avi' => 'video',
			'mkv' => 'video',
			'webm' => 'video',
		];

		if (isset($mapExt[$extension])) {
			return $mapExt[$extension];
		}

		// --- 3. MIME ---
		$mime = $this->obtainMime($ruta);

		if (str_starts_with($mime, 'image/')) return 'image';
		if (str_starts_with($mime, 'audio/')) return 'audio';
		if (str_starts_with($mime, 'video/')) return 'video';

		// --- 4. No se pudo determinar ---
		return null;
	}

	// ============================================================================
	// MARK: SET PROPERTIES
	// ============================================================================
	protected function setProperties(array $properties): void
	{
		foreach ($properties as $property) {
			$property();
		}

		$this->checkValidationErrors();
	}
}
