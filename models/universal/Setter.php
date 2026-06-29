<?php

declare(strict_types=1);

require_once __DIR__ . '/SQL.php';

abstract readonly class Setter extends SQL
{
	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: SET ID

	protected function setId(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => $min_range]])
			? $this->$name = (int) $value
			: $this->errors->setValidationError($error_message);
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
}
