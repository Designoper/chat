<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final class Mensaje extends MysqliConnect
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
		$min_range = 1;
		$error_message = "El id del recurso debe ser un número entero superior o igual a $min_range y solo contener números.";

		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$segments = explode('/', trim($path, '/'));
		$value = end($segments);

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_mensaje = (int) $value
			: $this->setValidationError($error_message);
	}

	private function setContenido(): void
	{
		$name = 'contenido';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->setValidationError($error_message)
			: $this->contenido = $value;
	}

	private function setIdReceptor(string $method): void
	{
		$method = match ($method) {
			'$_GET' => $_GET,
			'$_POST' => $_POST,
		};

		$name = 'id_receptor';
		$value = $method[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_receptor = (int) $value
			: $this->setValidationError($error_message);
	}

	private function setIdGrupo(string $method): void
	{
		$method = match ($method) {
			'$_GET' => $_GET,
			'$_POST' => $_POST,
		};

		$name = 'id_grupo';
		$value = $method[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_grupo = (int) $value
			: $this->setValidationError($error_message);
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_receptor IS NULL
			ORDER BY fecha_envio ASC";

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
		$this->authEndpoint();
		$this->setIdReceptor('$_GET');

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$id_receptor = $this->id_receptor;

		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_receptor IS NOT NULL
			AND (
				(id_emisor = ? AND id_receptor = ?)
			OR (id_emisor = ? AND id_receptor = ?)
			)
			ORDER BY fecha_envio ASC";

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
		$this->authEndpoint();
		$this->setIdGrupo('$_GET');

		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios on mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_grupo = ?
			ORDER BY fecha_envio ASC";

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

	// MARK: IS AUTOR MENSAJE

	private function isAutorMensaje(): bool
	{
		$this->setIdMensaje();

		$this->checkValidationErrors();

		$id_usuario = $this->id_emisor;
		$id_mensaje = $this->id_mensaje;

		$statement =
			"SELECT mensajes.id_emisor
			FROM mensajes
			WHERE mensajes.id_mensaje = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$id_mensaje
		);

		$query->execute();
		$autor = $query->get_result()->fetch_assoc();
		$query->close();

		return $autor['id_emisor'] === $id_usuario
			? true
			: false;
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		$this->authEndpoint();
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
		$this->authEndpoint();
		$this->setContenido();
		$this->setIdReceptor('$_POST');

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
		$this->authEndpoint();
		$this->setContenido();
		$this->setIdGrupo('$_POST');

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
		$this->setMessage("Mensaje grupal creado con éxito");
		$this->getResponse();
	}

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$autor = $this->isAutorMensaje();

		if (!$autor) {
			$this->setStatus(403);
			$this->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}

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
