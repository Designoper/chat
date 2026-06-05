<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

readonly class Mensaje extends MysqliConnect
{
	private int $id_mensaje;
	private string $contenido;
	protected int $id_receptor;
	protected int $id_grupo;
	private int $ultimo_id;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: SETTERS

	private function setContenido(): void
	{
		$name = 'contenido';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->contenido = $value;
	}

	protected function setId(string $name): void
	{
		$value = $_REQUEST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->$name = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	// MARK: GET ULTIMO MENSAJE

	public function getUltimoMensaje(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->getUltimoMensajeDirecto();
		}

		if (isset($_GET['id_grupo'])) {
			$this->getUltimoMensajeGrupal();
		}
	}

	// MARK: GET ULTIMO MENSAJE DIRECTO

	private function getUltimoMensajeDirecto(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT
				id_emisor,
				nombre_usuario,
				contenido,
				DATE_FORMAT(fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio
				FROM mensajes
				LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
				WHERE
					(id_emisor = ? AND id_receptor = ?)
					OR (id_emisor = ? AND id_receptor = ?)
				AND id_grupo IS NULL
				ORDER BY fecha_envio DESC
				LIMIT 1";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$id_usuario,
			$id_receptor,
			$id_receptor,
			$id_usuario
		);

		$query->execute();

		$ultimo_mensaje = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último mensaje directo obtenido con éxito';
		$this->content =
			$ultimo_mensaje ? $ultimo_mensaje : [];
		$this->sendResponse();
	}

	// MARK: GET ULTIMO MENSAJE GRUPAL

	private function getUltimoMensajeGrupal(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT
				id_emisor,
				nombre_usuario,
				contenido,
				DATE_FORMAT(fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio
				FROM mensajes
				LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
				WHERE id_grupo = ?
				AND id_receptor IS NULL
				ORDER BY fecha_envio DESC
				LIMIT 1";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_grupo,
		);

		$query->execute();

		$ultimo_mensaje = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último mensaje grupal obtenido con éxito';
		$this->content = $ultimo_mensaje ? $ultimo_mensaje : [];
		$this->sendResponse();
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
	}

	// MARK: GET ULTIMO ID DIRECTO

	private function getUltimoIdDirecto(): void
	{
		$this->setId('id_receptor');
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0) AS id_mensaje";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor
		);

		$query->execute();

		$last_id = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último id directo obtenido con éxito';
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: GET ULTIMO ID GRUPAL

	private function getUltimoIdGrupal(): void
	{
		$this->setId('id_grupo');
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0) AS id_mensaje";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();

		$last_id = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último id grupal obtenido con éxito';
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID

	public function setultimoIdLeido(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->setUltimoIdDirecto();
		}

		if (isset($_POST['id_grupo'])) {
			$this->setUltimoIdGrupal();
		}
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(): void
	{
		$this->setId('id_receptor');
		$this->setId('ultimo_id');
		$id_receptor = $this->id_receptor;
		$ultimo_id = $this->ultimo_id;
		$id_usuario = $this->session_user;

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

		$this->status = 201;
		$this->message = "Último id directo: $ultimo_id";
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(): void
	{
		$this->setId('id_grupo');
		$this->setId('ultimo_id');
		$id_grupo = $this->id_grupo;
		$ultimo_id = $this->ultimo_id;
		$id_usuario = $this->session_user;

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

		$this->status = 201;
		$this->message = "Último id grupal: $ultimo_id";
		$this->sendResponse();
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
	}

	// MARK: COUNT UNREAD DIRECT MESSAGES

	private function countUnreadDirectMessages(): void
	{
		$this->setId('id_receptor');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
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
		$this->content = $result;
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD GROUP MESSAGES

	private function countUnreadGroupMessages(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
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
		$this->content = $result;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->readMensajesDirectos();
		}

		if (isset($_GET['id_grupo'])) {
			$this->readMensajesGrupales();
		}
	}

	// MARK: READ MENSAJES DIRECTOS

	private function readMensajesDirectos(): void
	{
		$this->setId('id_receptor');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
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
			? 'Mensajes directos obtenidos.'
			: 'No hay ningún mensaje.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES GRUPALES

	private function readMensajesGrupales(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

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
			? 'Mensajes grupales obtenidos.'
			: 'No hay ningún mensaje.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: IS AUTOR MENSAJE

	private function isAutorMensaje(): void
	{
		$this->setId('id_mensaje');

		$this->checkValidationErrors();

		$id_usuario = $this->session_user;
		$id_mensaje = $this->id_mensaje;

		$statement =
			"SELECT id_emisor
			FROM mensajes
			WHERE id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_mensaje
		);

		$query->execute();
		$query->bind_result($autor);
		$query->fetch();
		$query->close();

		if ($autor !== $id_usuario) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	private function isMiembroGrupo(): void
	{
		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();
		$query->bind_result($rol);
		$query->fetch();
		$query->close();

		if ($rol === 'miembro' || $rol === 'fundador') {
			return;
		}

		$this->status = 403;
		$this->errors->setIntegrityError('No formas parte del grupo');
		$this->checkIntegrityErrors();
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->setId('id_receptor');
			$columna = 'id_receptor';
			$id_objetivo = $this->id_receptor;
			$tipo = 'directo';
		}

		if (isset($_POST['id_grupo'])) {
			$this->setId('id_grupo');
			$columna = 'id_grupo';
			$id_objetivo = $this->id_grupo;
			$tipo = 'grupal';
		}

		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
		$contenido = $this->contenido;

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, $columna)
			VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_objetivo
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje $tipo creado con éxito";
		$this->sendResponse();
	}

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$this->isAutorMensaje();

		$id_mensaje = $this->id_mensaje;
		$id_emisor = $this->session_user;

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

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	private function getNuevosMensajesDirectos(): array
	{
		$id_receptor = $this->id_receptor;
		$id_emisor = $this->session_user;

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

	private function getNuevosMensajesGrupales(): array
	{
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

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
		ignore_user_abort(false);

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

		if (isset($_GET['id_receptor'])) {
			$this->setId('id_receptor');
			$mensajes = fn() => $this->getNuevosMensajesDirectos();
		} else if (isset($_GET['id_grupo'])) {
			$this->setId('id_grupo');
			$mensajes = fn() => $this->getNuevosMensajesGrupales();
		}

		$this->checkValidationErrors();

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

				echo "event: new mensaje\n";
				echo "data: " . json_encode($ultimo_id) . "\n\n";
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
