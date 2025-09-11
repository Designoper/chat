<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Mensaje extends ApiResponse
{
	private readonly string $contenido;
	private readonly string $fecha;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getContenido(): string
	{
		return $this->contenido;
	}

	// MARK: SETTERS

	private function setContenido(): void
	{
		$value = $_POST['contenido'] ?? "";

		$this->contenido = $value;
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$statement = 'SELECT * FROM mensajes
		ORDER BY id_mensaje DESC';

		$query = $this->getConnection()->prepare($statement);

		$query->execute();

		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$mensajes
			? 'Mensajes obtenidos.'
			: 'No hay ningún mensaje.';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($mensajes);
		$this->getResponse();
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		$this->setContenido();

		$this->checkValidationErrors();

		$contenido = $this->getContenido();

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido)
			VALUES (?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"s",
			$contenido
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Mensaje creado con éxito");
		$this->getResponse();
	}
}
