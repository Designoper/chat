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
	private ?int $ultimo_id;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
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

	private function setultimoId(): void
	{
		$name = 'ultimo_id';

		if (isset($_POST[$name]) && $_POST[$name] === "") {
			$this->ultimo_id = null;
			return;
		}

		$value = $_POST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->ultimo_id = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	// MARK: GET ULTIMO ID

	public function getUltimoIdMensaje(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->getUltimoIdDirecto();
		}

		if (isset($_GET['id_grupo'])) {
			$this->getUltimoIdGrupal();
		}

		$this->getUltimoIdPublico();
	}

	private function getUltimoIdPublico(): ?int
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"SELECT id_mensaje
			FROM ultimos_mensajes_leidos_publicos
			WHERE id_usuario = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario,
		);

		$query->execute();

		$query->bind_result($last_id);
		$query->fetch();

		$query->close();

		return (int) $last_id;
	}

	private function getUltimoIdDirecto(): void
	{
		$this->setIdReceptor();
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->id_emisor;

		$statement =
			"SELECT id_mensaje
			FROM ultimos_mensajes_leidos_directos
			WHERE id_usuario = ?
			AND id_receptor = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor
		);

		$query->execute();

		$query->bind_result($last_id);
		$query->fetch();

		$query->close();

		$this->status = 200;
		$this->message = 'Último id directo';
		$this->content = ['id' => $last_id];
		$this->sendResponse();
	}

	private function getUltimoIdGrupal(): ?int
	{
		$this->setIdGrupo();
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->id_emisor;

		$statement =
			"SELECT id_mensaje
			FROM ultimos_mensajes_leidos_grupales
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();

		$query->bind_result($last_id);
		$query->fetch();

		$query->close();

		return (int) $last_id;
	}

	// MARK: SET ULTIMO ID MENSAJE

	// public function setUltimoIdMensaje(): void
	// {
	// 	$this->setultimoId();

	// 	if (isset($_POST['ultimo_id']) && isset($_POST['id_receptor'])) {
	// 		$this->setUltimoIdDirecto();
	// 	}

	// 	if (isset($_POST['ultimo_id']) && isset($_POST['id_grupo'])) {
	// 		$this->setUltimoIdGrupal();
	// 	}

	// 	if (isset($_POST['ultimo_id'])) {
	// 		$this->setUltimoIdPublico();
	// 	}
	// }

	// MARK: SET ULTIMO ID PUBLICO

	private function setUltimoIdPublico(int $ultimo_id): void
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_publicos (id_usuario, id_mensaje)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iii",
			$id_usuario,
			$ultimo_id,
			$ultimo_id
		);

		$query->execute();
		$query->close();
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(int $id_receptor, int $ultimo_id)
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_directos (id_usuario, id_receptor, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_usuario,
			$id_receptor,
			$ultimo_id,
			$ultimo_id
		);

		$query->execute();
		$query->close();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(int $id_grupo, int $ultimo_id): void
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_grupales (id_usuario, id_grupo, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_usuario,
			$id_grupo,
			$ultimo_id,
			$ultimo_id
		);

		$query->execute();
		$query->close();
	}

	// MARK: COUNT UNREAD MESSAGES

	public function countUnreadMessages(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->countUnreadDirectMessages();
		}

		if (isset($_GET['id_grupo'])) {
			$this->countUnreadGroupMessages();
		}

		if (count($_GET) === 0) {
			$this->countUnreadPublicMessages();
		}
	}

	// MARK: COUNT UNREAD DIRECT MESSAGES

	private function countUnreadDirectMessages(): void
	{
		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$id_receptor = $this->id_receptor;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE mensajes.id_receptor = ?
			AND mensajes.id_emisor = ?
			AND mensajes.id_grupo IS NULL
			AND mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0)";

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

	// MARK: COUNT UNREAD GROUP MESSAGES

	private function countUnreadGroupMessages(): void
	{
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE mensajes.id_grupo = ?
			AND mensajes.id_emisor != ?
			AND mensajes.id_receptor IS NULL
			AND mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_grupo,
			$id_emisor,
			$id_emisor,
			$id_grupo
		);

		$query->execute();
		$result = $query->get_result()->fetch_assoc();
		$query->close();

		$this->status = 200;
		$this->message = 'Número de mensajes grupales no leídos obtenido con éxito';
		$this->content = ['num_mensajes' => $result['num_mensajes']];
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD PUBLIC MESSAGES

	private function countUnreadPublicMessages(): void
	{
		$id_emisor = $this->id_emisor;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE mensajes.id_receptor IS NULL
			AND mensajes.id_grupo IS NULL
			AND mensajes.id_emisor != ?
			AND mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_publicos
				WHERE id_usuario = ?
			), 0)";

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
		// $allowed = ['id_receptor', 'id_grupo'];

		// // Si hay parámetros no permitidos
		// foreach ($_GET as $key => $value) {
		// 	if (!in_array($key, $allowed)) {
		// 		http_response_code(400);
		// 		echo json_encode(['error' => "Parámetro no permitido: $key"]);
		// 		return;
		// 	}
		// }

		// // Si llegan ambos, error
		// if (isset($_GET['id_receptor']) && isset($_GET['id_grupo'])) {
		// 	http_response_code(400);
		// 	echo json_encode(['error' => 'No puedes usar id_receptor e id_grupo a la vez']);
		// 	return;
		// }

		// if (isset($_GET['id_receptor'])) {
		// 	$this->readMensajesDirectos();
		// 	return;
		// }

		// if (isset($_GET['id_grupo'])) {
		// 	$this->readMensajesGrupales();
		// 	return;
		// }

		// // Ningún parámetro → mensajes públicos
		// $this->readMensajesPublicos();

		if (isset($_GET['id_receptor'])) {
			$this->readMensajesDirectos();
		}

		if (isset($_GET['id_grupo'])) {
			$this->readMensajesGrupales();
		}

		if (count($_GET) === 0) {
			$this->readMensajesPublicos();
		}
	}

	// MARK: READ MENSAJES PUBLICOS

	private function readMensajesPublicos(): void
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
		$this->sendResponse();
	}

	// MARK: READ MENSAJES DIRECTOS

	private function readMensajesDirectos(): void
	{
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

		if (!empty($mensajes)) {
			$ultimo = end($mensajes);
			$fin = $ultimo['id_mensaje'];
			$this->setUltimoIdDirecto($id_receptor, $fin);
		}

		// else $this->setUltimoIdDirecto($id_receptor, 0);

		$message =
			$mensajes
			? 'Mensajes obtenidos.'
			: 'No hay ningún mensaje.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES GRUPALES

	private function readMensajesGrupales(): void
	{
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
			$this->checkIntegrityErrors();
		}
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->createMensajeDirecto();
		}

		if (isset($_POST['id_grupo'])) {
			$this->createMensajeGrupal();
		}

		$this->createMensajePublico();
	}

	// MARK: CREATE MENSAJE PUBLICO

	public function createMensajePublico(): void
	{
		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

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
		$this->message = "Mensaje público creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setContenido();
		$this->setIdReceptor();

		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;

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
		$this->message = "Mensaje directo creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->setContenido();
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_emisor = $this->id_emisor;
		$contenido = $this->contenido;
		$id_grupo = $this->id_grupo;

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

	private function getNuevosMensajesPublicos(): array
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
            WHERE mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_publicos
				WHERE id_usuario = ?
			), 0)
            AND mensajes.id_receptor IS NULL
			AND mensajes.id_grupo IS NULL
            ORDER BY mensajes.id_mensaje ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		return $mensajes;
	}

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	private function getNuevosMensajesDirectos(int $id_receptor): array
	{
		$id_emisor = $this->id_emisor;

		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0)
			AND (
				(id_emisor = ? AND id_receptor = ?)
				OR (id_emisor = ? AND id_receptor = ?)
			)
			AND mensajes.id_grupo IS NULL
			ORDER BY mensajes.id_mensaje ASC";


		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiiiii",
			$id_emisor,
			$id_receptor,
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

	// MARK: GET NUEVOS MENSAJES GRUPALES

	private function getNuevosMensajesGrupales(int $id_grupo): array
	{
		$id_usuario = $this->id_emisor;
		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
            WHERE mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0)
            AND mensajes.id_receptor IS NULL
			AND mensajes.id_grupo = ?
            ORDER BY mensajes.id_mensaje ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iii",
			$id_usuario,
			$id_grupo,
			$id_grupo
		);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		return $mensajes;
	}

	// MARK: STREAM MENSAJES

	public function streamMensajes(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		set_time_limit(0);
		ignore_user_abort(true);

		// Limpia buffers previos
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Headers SSE
		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
		header("Connection: keep-alive");
		header("X-Accel-Buffering: no");

		ini_set('output_buffering', 'off');
		ini_set('zlib.output_compression', 0);

		// Forzar flush inicial
		echo str_pad('', 4096) . "\n";
		flush();

		// $this->setIdReceptor();
		// $id_receptor = $this->id_receptor;

		$id_receptor = isset($_GET["id_receptor"]) ? (int) $_GET["id_receptor"] : null;
		$id_grupo    = isset($_GET["id_grupo"])    ? (int) $_GET["id_grupo"]    : null;

		if (isset($_GET["id_receptor"])) {
			// $ultimo_id = $this->getUltimoIdDirecto($id_receptor);
			$mensajes = fn() => $this->getNuevosMensajesDirectos($id_receptor);
			$setID = fn($test) => $this->setUltimoIdDirecto($id_receptor, $test);
		} else if ($id_grupo) {
			// $ultimo_id = $this->getUltimoIdGrupal($id_grupo);
			$mensajes = fn() => $this->getNuevosMensajesGrupales($id_grupo);
			$setID = fn($test) => $this->setUltimoIdGrupal($id_grupo, $test);
		} else {
			// $ultimo_id = $this->getUltimoIdPublico();
			$mensajes = fn() => $this->getNuevosMensajesPublicos();
			$setID = fn($test) => $this->setUltimoIdPublico($test);
		}

		$lastPing = 0;

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$mensajesObtenidos = $mensajes();

			if (!empty($mensajesObtenidos)) {

				foreach ($mensajesObtenidos as $m) {
					$ultimo_id = $m["id_mensaje"];

					echo "event: mensaje\n";
					echo "data: " . json_encode($m) . "\n\n";
				}
				// echo "event: new mensaje\n";
				// echo "data: " . json_encode($ultimo_id) . "\n\n";
				$setID($ultimo_id);
			}

			if (time() - $lastPing > 10) {
				echo "event: ping\n";
				echo "data: keepalive\n\n";
				$lastPing = time();
			}

			@ob_flush();
			@flush();

			usleep(300000); // 0.3s
		}
	}
}
