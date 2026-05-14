<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Conexion extends MysqliConnect
{
	private int $id_usuario;
	private int $id_receptor;
	private int $id_grupo;
	private int $estado;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
		$this->id_usuario = $this->session_user;
	}

	// MARK: SETTERS

	private function setEstado(): void
	{
		$name = 'estado';
		$value = $_POST[$name] ?? null;
		$min_range = 0;
		$max_range = 1;
		$error_message = "El campo $name debe ser $min_range o $max_range.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range, "max_range" => $max_range)))
			? $this->estado = (int) $value
			: $this->errors->setValidationError($error_message);
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

	// MARK: SET CONEXION

	public function setConexion(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->setConexionDirecta();
		}

		if (isset($_POST['id_grupo'])) {
			$this->setConexionGrupal();
		}

		$this->setConexionPublica();
	}

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
		$this->message = "Conexion pública actualizada";
		$this->sendResponse();
	}

	private function setConexionDirecta(): void
	{
		$this->setIdReceptor();
		$id_usuario = $this->id_usuario;
		$id_receptor = $this->id_receptor;

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
		$this->message = "Conexion directa actualizada";
		$this->sendResponse();
	}

	private function setConexionGrupal(): void
	{
		$this->setIdGrupo();
		$id_usuario = $this->id_usuario;
		$id_grupo = $this->id_grupo;

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
		$this->message = "Conexion grupal actualizada";
		$this->sendResponse();
	}

	private function getConexionPublica(): int
	{
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT COALESCE((
				SELECT last_seen
				FROM conexion_publica
				WHERE id_usuario = ?
			), 0) AS last_seen";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$conexion = $conexion['last_seen'];

		$query->close();

		return $conexion;
	}

	private function getConexionDirecta(int $id_receptor): int
	{
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

		$conexion = $query->get_result()->fetch_assoc();

		$conexion = $conexion['last_seen'];

		$query->close();

		return $conexion;
	}

	private function getConexionGrupal(int $id_grupo): int
	{
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT last_seen
			 FROM conexion_grupal
			 WHERE id_usuario = ?
			 AND id_grupo = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$conexion = $conexion['last_seen'];

		$query->close();

		return $conexion;
	}

	// MARK: DECIDE

	private function decide(int $id_receptor, int $id_grupo): int
	{
		if (isset($_GET['id_receptor'])) {
			$conexion = $this->getConexionDirecta($id_receptor);
			return $conexion;
		}

		if (isset($_GET['id_grupo'])) {
			$conexion = $this->getConexionGrupal($id_grupo);
			return $conexion;
		}

		$conexion = $this->getConexionPublica();
		return $conexion;

		// echo "event: error\n";
		// echo "data: Tipo de stream no válido\n\n";
		// flush();
		// exit;
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

		// Enviar evento real inmediatamente
		// echo "event: open\n";
		// echo "data: ok\n\n";
		// @ob_flush();
		// @flush();


		// // Forzar flush inicial
		echo str_pad('', 4096) . "\n";
		flush();

		$id_receptor = (int) ($_GET["id_receptor"] ?? 0);
		$id_grupo =   (int) ($_GET["id_grupo"] ?? 0);

		$lastPing = 0;

		$actualTime = time();
		$conexion = $this->decide($id_receptor, $id_grupo);
		$estado = 0;

		if ($actualTime - $conexion > 20) {
			$estado = 'offline';
		} else $estado = 'online';

		echo "event: initial state\n";
		echo "data: $estado\n\n";

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$conexion = $this->decide($id_receptor, $id_grupo);
			$newTime = time();

			$nuevoEstado = ($newTime - $conexion > 20) ? 'offline' : 'online';

			if ($nuevoEstado !== $estado) {
				$estado = $nuevoEstado;
				echo "event: cambio\n";
				echo "data: $estado\n\n";
			}

			if (time() - $lastPing >= 15) {
				echo "event: ping\n";
				echo "data: {}\n\n";
				$lastPing = time();
			}

			// 🔥 Mantener viva la conexión en Hostinger
			// echo ": keepalive\n\n";

			@ob_flush();
			flush();

			usleep(300000); // 0.3s como el otro stream
		}
	}
}
