<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Conexion extends MysqliConnect
{
	private int $id_usuario;
	private int $id_receptor;
	private int $id_grupo;
	private int $estado;
	// private string $nombre_usuario;
	// private string $password;

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
		$error_message = "El campo $name no puede estar vacío.";

		$this->estado = (int) $value;
	}

	// MARK: READ

	public function setConexionPublica(): void
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
		// $this->setIdReceptor();
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
		// $this->setIdGrupo();
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

		// $this->setConexionPublica(1);

		while (true) {

			if (connection_aborted()) {
				// $this->setConexionPublica(0);
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

				if ($elapsed - $lastPing >= 5) {
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
