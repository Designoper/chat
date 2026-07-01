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
		$max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $max_length carácteres.";

		empty($value) || strlen($value) > $max_length
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET PASSWORD

	protected function setPassword(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$max_length = 20;
		$error_message = "El campo $name no puede estar vacío ni superar los $max_length carácteres.";

		empty($value) || strlen($value) > $max_length
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

	// MARK: SET IMAGEN

	protected function setImagen(string $name): void
	{
		$filesUploaded = $this->flattenFilesArray($name);

		if (count($filesUploaded) === 0) {
			$this->$name = null;
			return;
		}

		if (count($filesUploaded) > 1) {
			$this->errors->setValidationError('Solo se puede subir una imagen.');
			return;
		}

		$imagen = $filesUploaded[0];

		$file_type = exif_imagetype($imagen['tmp_name']);
		$allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

		// if (!in_array($file_type, $allowed_types)) {
		// 	$this->errors->setValidationError("Solo se permiten imágenes JPEG y PNG.");
		// }

		// if ($imagen['size'] > 1048576) {
		// 	$this->errors->setValidationError('La imagen no puede superar 1MB.');
		// }

		$this->$name = $imagen;
	}
}
