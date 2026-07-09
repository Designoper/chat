<?php

declare(strict_types=1);

require_once __DIR__ . "/File.php";

abstract readonly class Validator extends File
{
	protected function __construct()
	{
		parent::__construct();
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

	// ============================================================================
	// MARK: SET ULID
	// ============================================================================
	protected function setUlid(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$ulid_length = 26;
		$error_message = "El campo $name no puede estar vacío y debe contener $ulid_length carácteres.";

		empty($value) || strlen($value) !== $ulid_length
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
		$error_message = "El campo $name no puede estar vacío y debe contener exactamente $codigo_length carácteres.";

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
			$this->errors->setValidationError("No has subido ningún archivo.");
			$this->checkValidationErrors();
		}

		if (count($filesUploaded) > 1) {
			$this->errors->setValidationError("Solo se puede subir un archivo.");
			$this->checkValidationErrors();
		}

		$archivo = $filesUploaded[0];

		$tipoDetectado = $this->detectarTipoArchivo($archivo["tmp_name"], $archivo["name"]);

		$this->validarArchivoSeguro($archivo["tmp_name"], $tipoDetectado, $tipoEsperado);

		$this->$name = $archivo;
	}

	// ============================================================================
	// MARK: GET MAGIC NUMBER
	// ============================================================================
	private function getMagicNumber(string $filename): string
	{
		$stream = fopen($filename, "rb");
		$bytes = fread($stream, 32);
		fclose($stream);

		$magicNumber = bin2hex($bytes);

		return $magicNumber;
	}

	// ============================================================================
	// MARK: DETECTAR TIPO ARCHIVO
	// ============================================================================
	private function detectarTipoArchivo(string $filename, string $path): ?FileTypes
	{
		// --- 0. Extensión ---
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		// Bloqueo inmediato de SVG
		if ($extension === "svg") {
			$this->errors->setValidationError("Los archivos SVG no están permitidos.");
			$this->checkValidationErrors();
		}

		// Corrección rápida para M4A
		if ($extension === "m4a") {
			return FileTypes::Audio;
		}

		$magicNumber = $this->getMagicNumber($filename);

		// Imágenes
		if (str_starts_with($magicNumber, "ffd8ff")) return FileTypes::Image; // JPEG
		if (str_starts_with($magicNumber, "89504e47")) return FileTypes::Image; // PNG
		if (str_starts_with($magicNumber, "47494638")) return FileTypes::Image; // GIF
		if (str_starts_with($magicNumber, "52494646") && strpos($magicNumber, "57454250") !== false) return FileTypes::Image; // WebP

		// AVIF (ftypavif / ftypavis)
		if (
			strpos($magicNumber, "6674797061766966") !== false ||
			strpos($magicNumber, "6674797061766973") !== false
		) return FileTypes::Image;

		// Audio
		if (str_starts_with($magicNumber, "494433")) return FileTypes::Audio; // MP3 ID3
		if (str_starts_with($magicNumber, "4f676753")) return FileTypes::Audio; // OGG
		if (str_starts_with($magicNumber, "52494646") && strpos($magicNumber, "57415645") !== false) return FileTypes::Audio; // WAV

		// MP4 / MOV / M4A
		if (strpos($magicNumber, "667479706d7034") !== false) {
			return FileTypes::Video;
		}

		// WEBM / MKV (cabecera EBML)
		if (str_starts_with($magicNumber, "1a45dfa3")) {
			return FileTypes::Video;
		}

		// --- 2. Extensión ---
		$mapExt = [
			"jpg" => "image",
			"jpeg" => "image",
			"png" => "image",
			"gif" => "image",
			"webp" => "image",
			"avif" => "image",

			"mp3" => "audio",
			"wav" => "audio",
			"ogg" => "audio",
			"m4a" => "audio",

			"mp4" => "video",
			"mov" => "video",
			"avi" => "video",
			"mkv" => "video",
			"webm" => "video",
		];

		if (isset($mapExt[$extension])) {
			return $mapExt[$extension];
		}

		// --- 3. MIME ---
		$mime = $this->obtainMime($filename);

		if (str_starts_with($mime, "image/")) return FileTypes::Image;
		if (str_starts_with($mime, "audio/")) return FileTypes::Audio;
		if (str_starts_with($mime, "video/")) return FileTypes::Video;

		// --- 4. No se pudo determinar ---
		return null;
	}

	// ============================================================================
	// MARK: VALIDAR ARCHIVO SEGURO
	// ============================================================================
	private function validarArchivoSeguro(string $filename, ?FileTypes $tipoDetectado, FileTypes $tipoEsperado): void
	{
		if ($tipoDetectado === null) {
			$this->errors->setValidationError("No se pudo determinar el tipo de archivo.");
			$this->checkValidationErrors();
		}

		if ($tipoDetectado !== $tipoEsperado) {
			$this->errors->setValidationError("Se esperaba un archivo de tipo '$tipoEsperado', pero se recibió '$tipoDetectado'.");
			$this->checkValidationErrors();
		}

		switch ($tipoDetectado) {

			case FileTypes::Image:
				$info = @getimagesize($filename);
				if ($info === false) {
					$this->errors->setValidationError("La imagen no es válida o está corrupta.");
					$this->checkValidationErrors();
				}
				break;

			case FileTypes::Audio:
			case FileTypes::Video:
				if (filesize($filename) < 1024) {
					$this->errors->setValidationError("El archivo multimedia es demasiado pequeño para ser válido.");
					$this->checkValidationErrors();
				}
				break;

			default:
				$this->errors->setValidationError("Tipo de archivo no permitido.");
				$this->checkValidationErrors();
		}
	}
}
