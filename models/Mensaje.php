<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Mensaje extends MysqliConnect
{
	private int $id_mensaje;
	private string $contenido;
	private ?int $id_emisor;
	private int $id_receptor;
	private int $id_grupo;
	private int $ultimo_id;

	public function __construct()
	{
		parent::__construct();

		$this->id_emisor = $this->session_user;
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
			: $this->errors->setValidationError($error_message);
	}

	private function setContenido(): void
	{
		$name = 'contenido';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->contenido = $value;
	}

	private function setIdReceptor(): void
	{
		$method = match ($_SERVER['REQUEST_METHOD']) {
			'GET' => $_GET,
			'POST' => $_POST,
		};

		$name = 'id_receptor';
		$value = $method[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_receptor = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	private function setIdGrupo(): void
	{
		$method = match ($_SERVER['REQUEST_METHOD']) {
			'GET' => $_GET,
			'POST' => $_POST,
		};

		$name = 'id_grupo';
		$value = $method[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_grupo = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	// private function setUltimoId(): void
	// {
	// 	$name = 'ultimo_id';
	// 	$value = $_GET[$name] ?? null;
	// 	$min_range = 1;
	// 	$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

	// 	filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
	// 		? $this->ultimo_id = (int) $value
	// 		//NO FUNCIONA
	// 		// : $this->errors->setValidationError($error_message);
	// 		: null;
	// }

	// MARK: ULTIMA CONEXION PUBLICA

	public function setUltimaConexionPublica(): void
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"INSERT INTO conexion (id_usuario, ultima_conexion)
			VALUES (?, CURRENT_TIMESTAMP)
			ON DUPLICATE KEY
			UPDATE ultima_conexion = CURRENT_TIMESTAMP";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario,
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = 'Última conexión pública actualizada con éxito';
		$this->sendResponse();
	}

	// MARK: ULTIMA CONEXION DIRECTA

	public function setUltimaConexionDirecta(): void
	{
		$this->setIdReceptor();
		$id_usuario = $this->id_emisor;
		$id_receptor = $this->id_receptor;

		$statement =
			"INSERT INTO conexion (id_usuario, id_receptor, ultima_conexion)
			VALUES (?, ?, CURRENT_TIMESTAMP)
			ON DUPLICATE KEY
			UPDATE ultima_conexion = CURRENT_TIMESTAMP";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor,
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = 'Última conexión directa actualizada con éxito';
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD DIRECT MESSAGES

	public function countUnreadDirectMessages(): void
	{
		$this->authEndpoint();

		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$id_receptor = $this->id_receptor;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE mensajes.id_receptor = ?
			AND mensajes.id_emisor = ?
			AND mensajes.fecha_envio > COALESCE((
				SELECT ultima_conexion
				FROM conexion
				WHERE id_usuario = ?
				AND id_receptor = ?
				AND id_grupo IS NULL
			), '1970-01-01 00:00:01')";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_emisor,
			$id_receptor,
			$id_emisor,
			$id_receptor
		);

		$query->execute();
		$result = $query->get_result()->fetch_assoc();
		$query->close();

		$this->status = 200;
		$this->message = 'Número de mensajes directos no leídos obtenido con éxito';
		$this->content = ['num_mensajes' => $result['num_mensajes']];
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD PUBLIC MESSAGES

	public function countUnreadPublicMessages(): void
	{
		$this->authEndpoint();

		$id_emisor = $this->id_emisor;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE mensajes.id_receptor IS NULL
			AND mensajes.id_grupo IS NULL
			AND mensajes.id_emisor != ?
			AND mensajes.fecha_envio > COALESCE((
				SELECT ultima_conexion
				FROM conexion
				WHERE id_usuario = ?
				AND id_receptor IS NULL
				AND id_grupo IS NULL
			), '1970-01-01 00:00:01')";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_emisor,
			$id_emisor
		);

		$query->execute();
		$result = $query->get_result()->fetch_assoc();
		$query->close();

		$this->status = 200;
		$this->message = 'Número de mensajes públicos no leídos obtenido con éxito';
		$this->content = ['num_mensajes' => $result['num_mensajes']];
		$this->sendResponse();
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		$this->authEndpoint();

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

		$query = $this->connection->prepare($statement);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$mensajes
			? 'Mensajes obtenidos.'
			: 'No hay ningún mensaje.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		// $this->setUltimaConexionPublica();
		$this->sendResponse();
	}

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$this->authEndpoint();
		$this->setIdReceptor();

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

		$query = $this->connection->prepare($statement);

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

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->setUltimaConexionDirecta();
		$this->sendResponse();
	}

	// MARK: READ MENSAJES GRUPALES

	public function readMensajesGrupales(): void
	{
		$this->authEndpoint();
		$this->setIdGrupo();

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

		$query = $this->connection->prepare($statement);

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

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: IS AUTOR MENSAJE

	private function isAutorMensaje(): void
	{
		$this->setIdMensaje();

		$this->checkValidationErrors();

		$id_usuario = $this->id_emisor;
		$id_mensaje = $this->id_mensaje;

		$statement =
			"SELECT mensajes.id_emisor
			FROM mensajes
			WHERE mensajes.id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_mensaje
		);

		$query->execute();
		$autor = $query->get_result()->fetch_assoc();
		$query->close();

		if ($autor['id_emisor'] !== $id_usuario) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			// $this->checkIntegrityErrors();
		}
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		$this->authEndpoint();
		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

		// $this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor)
			VALUES (?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"si",
			$contenido,
			$id_emisor
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->authEndpoint();
		$this->setContenido();
		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

		// $this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_receptor)
			VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_receptor
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->authEndpoint();
		$this->setContenido();
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;
		$id_grupo = $this->id_grupo;

		// $this->checkIntegrityErrors();

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_grupo)
			VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_grupo
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje grupal creado con éxito";
		$this->sendResponse();
	}

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$this->isAutorMensaje();

		$id_mensaje = $this->id_mensaje;
		$id_emisor = $this->id_emisor;

		// $this->checkIntegrityErrors();

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_mensaje,
			$id_emisor
		);

		$query->execute();
		$num_filas = $query->affected_rows;
		$query->close();

		if ($num_filas === 1) {
			$this->status = 204;
		} else {
			$this->status = 404;
			$this->message = '¡El mensaje solicitado no existe!';
		}
		$this->sendResponse();
	}

	// MARK: GET NUEVOS MENSAJES PUBLICOS

	public function getNuevosMensajesPublicos(int $ultimo_id)
	{
		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
            WHERE mensajes.id_mensaje > ?
            AND mensajes.id_receptor IS NULL
			AND mensajes.id_grupo IS NULL
            ORDER BY mensajes.id_mensaje ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$ultimo_id
		);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		return $mensajes;
	}

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	public function getNuevosMensajesDirectos(int $ultimo_id, int $id_emisor, int $id_receptor)
	{
		// $id_emisor = $_SESSION['id_usuario'];

		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
            WHERE mensajes.id_mensaje > ?
            AND mensajes.id_receptor IS NOT NULL
			AND (
				(id_emisor = ? AND id_receptor = ?)
				OR (id_emisor = ? AND id_receptor = ?)
			)
			AND mensajes.id_grupo IS NULL
            ORDER BY mensajes.id_mensaje ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiiii",
			$ultimo_id,
			$id_emisor,
			$id_receptor,
			$id_receptor,
			$id_emisor
		);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		return $mensajes;
	}
}
