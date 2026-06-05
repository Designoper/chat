<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajePermissions.php';

abstract readonly class MensajeCount extends MensajePermissions
{
	protected function __construct()
	{
		parent::__construct();
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

	// MARK: COUNT UNREAD PUBLIC MESSAGES

	private function countUnreadPublicMessages(): void
	{
		$id_emisor = $this->session_user;

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
		$this->content = $result;
		$this->sendResponse();
	}
}
