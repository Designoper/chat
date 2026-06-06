<?php

declare(strict_types=1);

require_once __DIR__ . '/Mensaje.php';

final readonly class Conexion extends Mensaje
{
	public function __construct()
	{
		parent::__construct();
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
	}

	// MARK: SET CONEXION DIRECTA

	private function setConexionDirecta(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO conexion_directa (id_usuario, id_receptor)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor,
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Última conexión directa establecida";
		$this->sendResponse();
	}

	// MARK: SET CONEXION GRUPAL

	private function setConexionGrupal(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO conexion_grupal (id_usuario, id_grupo)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo,
		);

		$query->execute();

		$this->status = 201;
		$this->message = "Última conexión grupal establecida";
		$this->sendResponse();
	}

	// MARK: GET CONEXION DIRECTA

	private function getConexionDirecta(): array
	{
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT nombre_usuario,
			COALESCE(DATE_FORMAT(conexion_directa.last_seen, '%Y-%m-%dT%H:%i:%sZ'),0) AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_directa.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_directa
				ON usuarios.id_usuario = conexion_directa.id_usuario
			AND conexion_directa.id_receptor = ?
			WHERE usuarios.id_usuario = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_receptor
		);

		$query->execute();

		$conexion = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		return $conexion;
	}

	// MARK: GET CONEXION GRUPAL

	private function getConexionGrupal(): array
	{
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT usuarios.nombre_usuario,
			DATE_FORMAT(conexion_grupal.last_seen, '%Y-%m-%dT%H:%i:%sZ') AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_directa.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_grupal
				ON usuarios.id_usuario = conexion_grupal.id_usuario
			LEFT JOIN membresias
				ON usuarios.id_usuario = membresias.id_usuario
			WHERE membresias.id_grupo = ?
			AND membresias.id_usuario != ?
			AND (membresias.rol = 'fundador' OR membresias.rol = 'miembro')
			ORDER BY usuarios.nombre_usuario ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_grupo,
			$id_usuario
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
			$this->setId('id_receptor');
			$getConexion = fn() => $this->getConexionDirecta();
			$tipo = "directo";
		}

		if (isset($_GET['id_grupo'])) {
			$this->setId('id_grupo');
			$getConexion = fn() => $this->getConexionGrupal();
			$tipo = "grupal";
		}

		$this->checkValidationErrors();

		$lastPing = 0;

		$array = [];

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$conexion = $getConexion();

			$newArray = [];

			foreach ($conexion as $c) {
				$newArray[] = [
					'usuario' => $c['nombre_usuario'],
					'estado'  => (time() - $c['last_seen_unix'] > 10)
						? $c['last_seen']
						: 'Online'
				];
			}

			if ($newArray !== $array) {
				echo "event: conexion $tipo\n";
				echo "data: " . json_encode($newArray) . "\n\n";
				$array = $newArray;
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
