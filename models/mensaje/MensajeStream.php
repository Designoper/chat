<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajeRead.php';

readonly class MensajeStream extends MensajeRead
{
	protected function __construct()
	{
		parent::__construct();
	}

	private function getNuevosMensajesPublicos(): array
	{
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
		} else $mensajes = fn() => $this->getNuevosMensajesPublicos();

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
