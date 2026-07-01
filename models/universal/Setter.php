<?php

declare(strict_types=1);

require_once __DIR__ . '/FileManager.php';

abstract readonly class Setter extends FileManager
{
	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: SET ULID

	protected function setUlid(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío y debe contener 26 carácteres.";

		strlen($value) !== 26
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET NOMBRE

	protected function setNombre(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío ni superar los 20 carácteres.";

		empty($value) || strlen($value) > 20
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET CODIGO

	protected function setCodigo(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío y debe contener 6 carácteres.";

		empty($value) || strlen($value) !== 6
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SET PASSWORD

	protected function setPassword(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío ni superar los 20 carácteres.";

		empty($value) || strlen($value) > 20
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
