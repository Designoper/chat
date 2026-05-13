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
		$this->setEstado();
		$estado = $this->estado;
		$id_usuario = $this->id_usuario;

		$statement =
			"INSERT INTO conexion_publica (conectado, id_usuario)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE conectado = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iii",
			$estado,
			$id_usuario,
			$estado
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Conexion actualizada";
		$this->sendResponse();
	}

	private function setConexionDirecta(): void
	{
		$this->setEstado();
		$this->setIdReceptor();
		$estado = $this->estado;
		$id_usuario = $this->id_usuario;
		$id_receptor = $this->id_receptor;

		$statement =
			"INSERT INTO conexion_directa (conectado, id_usuario, id_receptor)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE conectado = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$estado,
			$id_usuario,
			$id_receptor,
			$estado
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Conexion actualizada";
		$this->sendResponse();
	}

	private function setConexionGrupal(): void
	{
		$this->setEstado();
		$this->setIdGrupo();
		$estado = $this->estado;
		$id_usuario = $this->id_usuario;
		$id_grupo = $this->id_grupo;

		$statement =
			"INSERT INTO conexion_grupal (conectado, id_usuario, id_grupo)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE conectado = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiii",
			$estado,
			$id_usuario,
			$id_grupo,
			$estado
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Conexion actualizada";
		$this->sendResponse();
	}

	private function getConexionPublica(): bool
	{
		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT conectado
			 FROM conexion_publica
			 WHERE id_usuario = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$conexion = $conexion['conectado'];

		$query->close();

		return $conexion;
	}

	private function getConexionDirecta(): bool
	{
		$this->setIdReceptor();
		$id_usuario = $this->id_usuario;
		$id_receptor = $this->id_receptor;

		$statement =
			"SELECT conectado
			 FROM conexion_directa
			 WHERE id_usuario = ?
			 AND id_receptor = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$conexion = $conexion['conectado'];

		$query->close();

		return $conexion;
	}

	private function getConexionGrupal(): bool
	{
		$this->setIdGrupo();
		$id_usuario = $this->id_usuario;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT conectado
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

		$conexion = $conexion['conectado'];

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

		// Forzar flush inicial
		echo str_pad('', 4096) . "\n";
		flush();

		$id_receptor = (int) ($_GET["id_receptor"] ?? 0);
		$id_grupo =   (int) ($_GET["id_grupo"] ?? 0);
		$estado_conexion = false;

		$startTime = time();
		$lastPing = 0;

		while (true) {

			if (connection_aborted()) {
				break;
			}

			// $mensajes = $this->decide($ultimo_id, $id_receptor, $id_grupo);

			$estado_conexion_2 = $this->getConexionPublica();

			if ($estado_conexion_2 !== $estado_conexion) {

				echo "event: nuevo estado\n";
				echo "data: {}" . "\n\n";
				$estado_conexion = $estado_conexion_2;
			} else {
				// Heartbeat cada 15s
				$elapsed = time() - $startTime;

				if ($elapsed - $lastPing >= 15) {
					echo "event: ping\n";
					echo "data: {}\n\n";
					$lastPing = $elapsed;
				}

				@ob_flush();
				@flush();
				usleep(1000000); // 1s
			}
		}
	}
}
