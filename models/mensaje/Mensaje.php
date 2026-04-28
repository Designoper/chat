<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Mensaje extends ApiResponse
{
	private readonly int $id_mensaje;
	private readonly string $contenido;
	private ?int $id_emisor;
	private readonly int $id_receptor;
	private readonly int $id_grupo;

	public function __construct()
	{
		parent::__construct();

		$this->id_emisor = $this->getAuthenticatedUserId();
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
		$name = 'contenido';
		$value = $_POST[$name] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo $name no puede estar vacío.");
			return;
		}

		$this->contenido = $value;
	}

	private function setIdReceptorFromPost(): void
	{
		$name = 'id_receptor';
		$value = $_POST[$name] ?? null;
		$min = 1;

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
			$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
			return;
		}

		$this->id_receptor = (int) $value;
	}

	private function setIdReceptorFromGet(): void
	{
		$name = 'id_receptor';
		$value = $_GET[$name] ?? null;
		$min = 1;

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
			$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
			return;
		}

		$this->id_receptor = (int) $value;
	}

		private function setIdGrupo(): void
	{
		$name = 'id_grupo';
		$value = $_POST[$name] ?? null;
		$min = 1;

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
			$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
			return;
		}

		$this->id_grupo = (int) $value;
	}

	// MARK: AUTH

	public function auth(): void
	{
		if ($this->id_emisor === null) {
			$this->setStatus(401);
			$this->setIntegrityError('No hay sesión');
			$this->checkIntegrityErrors();
		}
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
		$this->auth();
		$this->setIdReceptorFromGet();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$id_receptor = $this->id_receptor;

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

	// MARK: READ MENSAJES GRUPALES

	public function readMensajesGrupales(): void
	{
		$this->auth();
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_creacion, '%Y-%m-%dT%H:%i:%sZ') AS fecha_creacion,
				mensajes.id_emisor,
				usuarios.nombre
			FROM mensajes
			WHERE mensajes.id_grupo = ?
			ORDER BY fecha_creacion";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$id_grupo
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
		$this->auth();
		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor)
			VALUES (?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"si",
			$contenido,
			$id_emisor
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
		$this->auth();
		$this->setContenido();
		$this->setIdReceptorFromPost();

		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

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

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->auth();
		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;
		$id_grupo = $this->id_grupo;

		$this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_grupo)
			VALUES (?, ?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_grupo
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
		$this->auth();
		$this->setIdMensaje();
		$this->checkValidationErrors();

		$id_mensaje = $this->id_mensaje;
		$id_emisor = $this->id_emisor;

		$this->checkIntegrityErrors();

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"ii",
			$id_mensaje,
			$id_emisor
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
