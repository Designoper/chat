<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class MensajeDirecto extends ApiResponse
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

	private function setIdReceptor(): void
	{
		$value = $_SESSION['id_receptor'] ?? null;

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

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$id_emisor = $_SESSION['id_usuario'];
		$this->setIdReceptor();

		$id_receptor = $this->getIdReceptor();

		$statement =
			'SELECT
				m.id_mensaje,
				m.contenido,
				m.fecha_creacion,
				m.id_emisor,
				m.id_receptor,
				ue.nombre AS nombre_emisor,
				ur.nombre AS nombre_receptor
			FROM mensajes_directos m
			JOIN usuarios ue ON m.id_emisor = ue.id_usuario
			JOIN usuarios ur ON m.id_receptor = ur.id_usuario
			WHERE (m.id_emisor = ? AND m.id_receptor = ?)
			OR (m.id_emisor = ? AND m.id_receptor = ?)
			AND m.id_emisor != ?
			ORDER BY m.fecha_creacion DESC';

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"iiiii",
			$id_emisor,
			$id_receptor,
			$id_receptor,
			$id_emisor,
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

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setContenido();
		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_emisor = $_SESSION['id_usuario'];
		$contenido = $this->getContenido();
		$id_receptor = $_SESSION['id_receptor'];

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes_directos (contenido, id_emisor, id_receptor)
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

	// MARK: DELETE MENSAJE DIRECTO

	// public function deleteMensajeDirecto(): void
	// {
	// 	$this->setIdMensaje();

	// 	$this->checkValidationErrors();

	// 	$id_mensaje = $this->getIdMensaje();
	// 	$id_usuario = $_SESSION['id_usuario'];

	// 	$this->checkIntegrityErrors();

	// 	$statement =
	// 		"DELETE FROM mensajes
	// 		WHERE id_mensaje = ?
	// 		AND id_usuario = ?";

	// 	$query = $this->getConnection()->prepare($statement);

	// 	$query->bind_param(
	// 		"ii",
	// 		$id_mensaje,
	// 		$id_usuario
	// 	);

	// 	$query->execute();
	// 	$num_filas = $query->affected_rows;
	// 	$query->close();

	// 	if ($num_filas === 1) {
	// 		$this->setStatus(204);
	// 	} else {
	// 		$this->setStatus(404);
	// 		$this->setMessage('¡El mensaje solicitado no existe!');
	// 	}
	// 	$this->getResponse();
}
