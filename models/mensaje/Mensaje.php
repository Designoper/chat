<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Mensaje extends ApiResponse
{
	private readonly string $contenido;
	private readonly string $fecha;
	private readonly int $id_usuario;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getContenido(): string
	{
		return $this->contenido;
	}

	private function getIdUsuario(): int
	{
		return $this->id_usuario;
	}

	// MARK: SETTERS

	private function setContenido(): void
	{
		$value = $_POST['contenido'] ?? "";

		$this->contenido = $value;
	}

	private function setIdUsuario(): void
	{
		$value = $_POST['id_usuario'] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo 'id_usuario' no puede estar vacío.");
			return;
		}

		$this->id_usuario = (int) $value;
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$statement = 'SELECT * FROM mensajes
		NATURAL JOIN usuarios
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
		$this->setIdUsuario();

		$this->checkValidationErrors();

		$contenido = $this->getContenido();
		$id_usuario = $this->getIdUsuario();

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_usuario)
			VALUES (?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"si",
			$contenido,
			$id_usuario
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Mensaje creado con éxito");
		$this->getResponse();
	}
}
