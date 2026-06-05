<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajeCreate.php';

abstract readonly class MensajeRead extends MensajeCreate
{
	protected function __construct()
	{
		parent::__construct();
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
			AND mensajes.id_grupo IS NULL
			ORDER BY fecha_envio ASC";

		$query = $this->connection->prepare($statement);

		$query->execute();
		$mensajes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$mensajes
			? 'Mensajes públicos obtenidos.'
			: 'No hay ningún mensaje.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES DIRECTOS

	private function readMensajesDirectos(): void
	{
		$this->setId('id_grupo');

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
}
