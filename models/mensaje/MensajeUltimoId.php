<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajePermissions.php';

abstract readonly class MensajeUltimoId extends MensajePermissions
{
	protected function __construct()
	{
		parent::__construct();
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

	// MARK: GET ULTIMO ID PUBLICO

	private function getUltimoIdPublico(): void
	{
		$id_usuario = $this->id_emisor;

		$statement =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_publicos
				WHERE id_usuario = ?
			), 0) AS id_mensaje";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario,
		);

		$query->execute();

		$last_id = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último id público obtenido con éxito';
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: GET ULTIMO ID DIRECTO

	private function getUltimoIdDirecto(): void
	{
		$this->setIdReceptor();
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->id_emisor;

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
		$this->setIdGrupo();
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->id_emisor;

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

		// if (isset($_POST['ultimo_id'])) {
		$this->setUltimoIdPublico();
		// }
	}

	// MARK: SET ULTIMO ID PUBLICO

	private function setUltimoIdPublico(): void
	{
		$this->setUltimoId();
		$ultimo_id = $this->ultimo_id;
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

		$this->status = 201;
		$this->message = "Último id público: $ultimo_id";
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(): void
	{
		$this->setIdReceptor();
		$this->setultimoId();
		$id_receptor = $this->id_receptor;
		$ultimo_id = $this->ultimo_id;
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

		$this->status = 201;
		$this->message = "Último id directo: $ultimo_id";
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(): void
	{
		$this->setIdGrupo();
		$this->setultimoId();
		$id_grupo = $this->id_grupo;
		$ultimo_id = $this->ultimo_id;
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

		$this->status = 201;
		$this->message = "Último id grupal: $ultimo_id";
		$this->sendResponse();
	}
}
