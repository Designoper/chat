<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Mensaje extends ApiResponse
{
	private readonly int $id_mensaje;
	private readonly string $contenido;
	private readonly string $fecha_creacion;
	private readonly int $id_usuario;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getIdMensaje(): int
	{
		return $this->id_mensaje;
	}

	private function getContenido(): string
	{
		return $this->contenido;
	}

	private function getIdUsuario(): int
	{
		return $this->id_usuario;
	}

	// MARK: SETTERS

	private function setIdMensaje(int $min = 1): void
	{
		$error_message = "El id del recurso debe ser un número entero superior o igual a $min y solo contener números.";

		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$segments = explode('/', trim($path, '/'));
		$value = end($segments);

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))
			? $this->id_mensaje = (int) $value
			: $this->setValidationError($error_message);
	}

	private function setContenido(): void
	{
		$value = $_POST['contenido'] ?? "";

		$this->contenido = $value;
	}

	private function setIdUsuario(): void
	{
		$value = $_SESSION['id_usuario'] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo 'id_usuario' no puede estar vacío.");
			return;
		}

		$this->id_usuario = (int) $value;
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$statement =
			'SELECT id_mensaje, contenido, fecha_creacion, id_usuario, nombre
			FROM mensajes
			NATURAL JOIN usuarios
			ORDER BY fecha_creacion DESC';

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
		$id_usuario = $_SESSION['id_usuario'];

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

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$this->setIdMensaje();

		$this->checkValidationErrors();

		$id_mensaje = $this->getIdMensaje();
		$id_usuario = $_SESSION['id_usuario'];

		$this->checkIntegrityErrors();

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_usuario = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"ii",
			$id_mensaje,
			$id_usuario
		);

		$query->execute();
		$num_filas = $query->affected_rows;
		$query->close();

		if ($num_filas === 1) {
			$this->setStatus(204);
		} else {
			$this->setStatus(404);
			$this->setMessage('¡El mensaje solicitado no existe!');
		}
		$this->getResponse();
	}
}
