<?php

declare(strict_types=1);

require_once __DIR__ . '/MysqliConnect.php';

enum SqlReturn
{
	case Array;
	case ArraySimple;
	case Id;
	case Bind;
}

abstract readonly class Helper extends MysqliConnect
{
	protected const string TEMPORAL_STRING = '%Y-%m-%dT%H:%i:%sZ';

	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: SETTERS

	protected function setId(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->$name = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	// protected function setNombre(string $name): void
	// {
	// 	$value = $_POST[$name] ?? null;
	// 	$error_message = "El nombre solo puede contener mayúsculas(A-Z), minúsculas(a-z), números(1-9) y guión bajo (_). Longitud de 3 a 20 carácteres.";

	// 	preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value)
	// 		? $this->$name = $value
	// 		: $this->errors->setValidationError($error_message);
	// }

	protected function setNombre(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío ni superar los 20 carácteres.";

		empty($value) || strlen($value) > 20
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	protected function setPassword(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	protected function setContenido(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->$name = $value;
	}

	// MARK: SQL ALL

	protected function sqlAll(string $statement, string $types, array $content, ?SqlReturn $type = null): mixed
	{
		$query = $this->connection->prepare($statement);

		$query->bind_param(
			$types,
			...$content
		);

		$query->execute();

		switch ($type) {
			case SqlReturn::Array:
				$result = $query->get_result()->fetch_all(MYSQLI_ASSOC);
				break;

			case SqlReturn::ArraySimple:
				$result = $query->get_result()->fetch_assoc();
				break;

			case SqlReturn::Id:
				$result = (int) $query->insert_id;
				break;

			case SqlReturn::Bind:
				$query->bind_result($result);
				$query->fetch();
				break;

			default:
				$result = null;
		}

		$query->close();

		return $result;
	}
}
