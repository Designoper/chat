<?php

declare(strict_types=1);

require_once __DIR__ . '/File.php';

abstract readonly class Setter extends File
{
	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: SET ULID

	protected function setUlid(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$ulid_length = 26;
		$error_message = "El campo $name no puede estar vacío y debe contener $ulid_length carácteres.";

		strlen($value) !== $ulid_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET NOMBRE

	protected function setNombre(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$nombre_max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $nombre_max_length carácteres.";

		empty($value) || strlen($value) > $nombre_max_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET PASSWORD

	protected function setPassword(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$password_max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $password_max_length carácteres.";

		empty($value) || strlen($value) > $password_max_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET CODIGO

	protected function setCodigo(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$codigo_length = 6;
		$error_message = "El campo $name no puede estar vacío y debe contener $codigo_length carácteres.";

		empty($value) || strlen($value) !== $codigo_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET CONTENIDO

	protected function setContenido(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET ARCHIVO

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

		$this->validarArchivoSeguro($archivo['tmp_name'], $tipoEsperado);

		$this->$name = $archivo;
	}

	// MARK: VALIDAR ARCHIVO SEGURO

	private function validarArchivoSeguro(string $ruta, FileTypes $tipoEsperado)
	{
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($ruta);

		if ($mime === 'image/svg+xml') {
			$this->errors->setValidationError('Los archivos SVG no están permitidos por razones de seguridad.');
			$this->checkValidationErrors();
		}

		$categoria = null;

		if (str_starts_with($mime, 'image/')) {
			$categoria = FileTypes::Image;
		} elseif (str_starts_with($mime, 'video/')) {
			$categoria = FileTypes::Video;
		} elseif (str_starts_with($mime, 'audio/')) {
			$categoria = FileTypes::Audio;
		} else {
			$this->errors->setValidationError('Tipo de archivo no permitido.');
			$this->checkValidationErrors();
		}

		if ($categoria !== $tipoEsperado) {
			$this->errors->setValidationError("Se esperaba un archivo de tipo {$tipoEsperado->name}, pero se recibió {$categoria->name}.");
			$this->checkValidationErrors();
		}

		switch ($categoria) {

			case FileTypes::Image:
				$info = @getimagesize($ruta);
				if ($info === false) {
					$this->errors->setValidationError('La imagen no es válida o está corrupta');
					$this->checkValidationErrors();
				}

			case FileTypes::Audio:
			case FileTypes::Video:
				if (filesize($ruta) < 1024) {
					$this->errors->setValidationError('El archivo multimedia es demasiado pequeño para ser válido');
					$this->checkValidationErrors();
				}
		}
	}
}
