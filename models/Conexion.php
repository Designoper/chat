<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Conexion extends MysqliConnect
{
	private int $id_usuario;
	private int $id_receptor;
	private int $id_grupo;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
		$this->id_usuario = $this->session_user;
	}

	// MARK: SETTERS

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

	// MARK: SET CONEXION

	public function setConexion(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->setConexionDirecta();
		}

		if (isset($_POST['id_grupo'])) {
			$this->setConexionGrupal();
		}

		if (count($_POST) === 0) {
			$this->setConexionPublica();
		}
	}

	// MARK: SET CONEXION PUBLICA

	private function setConexionPublica(): void
	{
		$id_usuario = $this->id_usuario;

		$statement =
			"INSERT INTO conexion_publica (id_usuario, last_seen)
			VALUES (?, UNIX_TIMESTAMP())
			ON DUPLICATE KEY UPDATE
				last_seen = UNIX_TIMESTAMP();";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario,
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Última conexion pública establecida";
		$this->sendResponse();
	}

	// MARK: SET CONEXION DIRECTA

	private function setConexionDirecta(): void
	{
		$this->setIdReceptor();
		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->id_usuario;

		$statement =
			"INSERT INTO conexion_directa (id_usuario, id_receptor, last_seen)
			VALUES (?, ?, UNIX_TIMESTAMP())
			ON DUPLICATE KEY UPDATE
				last_seen = UNIX_TIMESTAMP();";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor,
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Última conexion directa establecida";
		$this->sendResponse();
	}

	// MARK: SET CONEXION GRUPAL

	private function setConexionGrupal(): void
	{
		$this->setIdGrupo();
		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;
		$id_usuario = $this->id_usuario;

		$statement =
			"INSERT INTO conexion_grupal (id_usuario, id_grupo, last_seen)
			VALUES (?, ?, UNIX_TIMESTAMP())
			ON DUPLICATE KEY UPDATE
				last_seen = UNIX_TIMESTAMP();";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo,
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Última conexion grupal establecida";
		$this->sendResponse();
	}

	// MARK: GET CONEXION PUBLICA

	private function getConexionPublica(): array
	{
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT COALESCE((
				SELECT last_seen
				FROM conexion_publica
				WHERE id_usuario = !?
			), 0) AS last_seen";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		return $conexion;
	}

	// MARK: GET CONEXION DIRECTA

	private function getConexionDirecta(): int
	{
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT COALESCE((
				SELECT last_seen
				FROM conexion_directa
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0) AS last_seen";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_receptor,
			$id_usuario
		);

		$query->execute();

		// $query->store_result();

		// 🔥 NO usar get_result() en Hostinger
		$query->bind_result($last_seen);
		$query->fetch();

		$query->close();

		return (int) $last_seen;
	}

	// MARK: GET CONEXION GRUPAL

	private function getConexionGrupal(): array
	{
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT COALESCE((
				SELECT last_seen
				FROM conexion_grupal
				WHERE id_usuario != ?
				AND id_grupo = ?
			), 0) AS last_seen";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		return $conexion;
	}

	// MARK: STREAM CONEXION

	public function streamConexion(): void
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

		// // Forzar flush inicial
		echo str_pad('', 4096) . "\n";
		flush();

		if (isset($_GET['id_receptor'])) {
			$this->setIdReceptor();
			$this->checkValidationErrors();
			$getConexion = fn() => $this->getConexionDirecta();
		} else if (isset($_GET['id_grupo'])) {
			$this->setIdGrupo();
			$this->checkValidationErrors();
			$getConexion = fn() => $this->getConexionGrupal();
		} else {
			$getConexion = fn() => $this->getConexionPublica();
		}

		$lastPing = 0;

		$conexion = $getConexion();

		$estado = (time() - $conexion > 10)
			? 'offline'
			: 'online';

		echo "event: initial state\n";
		echo "data: $estado\n\n";

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$conexion = $getConexion();

			$nuevoEstado = (time() - $conexion > 10)
				? 'offline'
				: 'online';

			if ($nuevoEstado !== $estado) {
				echo "event: cambio\n";
				echo "data: $nuevoEstado\n\n";
				$estado = $nuevoEstado;
			}

			if (time() - $lastPing > 5) {
				echo "event: ping\n";
				echo "data: keepalive\n\n";
				$lastPing = time();
			}

			@ob_flush();
			@flush();

			usleep(2000000);
		}
	}
}
