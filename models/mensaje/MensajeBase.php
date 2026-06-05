<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/MysqliConnect.php';

abstract readonly class MensajeBase extends MysqliConnect
{
	protected int $id_mensaje;
	protected string $contenido;
	protected int $id_receptor;
	protected int $id_grupo;
	protected int $ultimo_id;

	protected function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	protected function setId(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->$name = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	protected function setContenido(): void
	{
		$name = 'contenido';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->contenido = $value;
	}
}
