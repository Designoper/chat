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

	// MARK: GENERATE ULID

	protected function ulid(): string
	{
		$time = microtime(true) * 1000;
		$time = base_convert((string) (int) $time, 10, 32);
		$time = str_pad($time, 10, '0', STR_PAD_LEFT);

		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= base_convert((string) random_int(0, 31), 10, 32);
		}

		return strtoupper($time . $rand);
	}
}
