<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Mensaje extends ApiResponse
{
	private readonly int $id_mensaje;
	private readonly string $contenido;
	private readonly int $id_emisor;
	private readonly int $id_receptor;

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

	private function getIdReceptor(): int
	{
		return $this->id_receptor;
	}

	// MARK: SETTERS

	private function setIdMensaje(): void
	{
		$min = 1;
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

	private function setIdReceptor(): void
	{
		$value = $_GET['id_receptor'] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo 'id_receptor' no puede estar vacío.");
			return;
		}

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
			$this->setValidationError("El campo 'id_receptor' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->id_receptor = (int) $value;
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_creacion, '%Y-%m-%dT%H:%i:%sZ') AS fecha_creacion,
				mensajes.id_emisor,
				usuarios.nombre
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_receptor IS NULL
			ORDER BY fecha_creacion";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$mensajes
			? 'Mensajes obtenidos.'
			: 'No hay ningún mensaje.';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($mensajes);
		$this->getResponse();
	}

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$id_emisor = $_SESSION['id_usuario'];
		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_receptor = $this->getIdReceptor();

		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_creacion, '%Y-%m-%dT%H:%i:%sZ') AS fecha_creacion,
				mensajes.id_emisor,
				usuarios.nombre
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_receptor IS NOT NULL
			AND (
				(id_emisor = ? AND id_receptor = ?)
			OR (id_emisor = ? AND id_receptor = ?)
			)
			ORDER BY fecha_creacion";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_emisor,
			$id_receptor,
			$id_receptor,
			$id_emisor
		);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$mensajes
			? 'Mensajes obtenidos.'
			: 'No hay ningún mensaje.';

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

		$id_usuario = $_SESSION['id_usuario'];
		$contenido = $this->getContenido();

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor)
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

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setContenido();
		// $this->setIdReceptor();

		$this->checkValidationErrors();

		$id_emisor = $_SESSION['id_usuario'];
		$contenido = $this->getContenido();
		$id_receptor = $_POST["id_receptor"];

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_receptor)
			VALUES (?, ?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_receptor
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
			AND id_emisor = ?";

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
