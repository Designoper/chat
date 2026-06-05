<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajePermissions.php';

abstract readonly class MensajeUltimoRead extends MensajePermissions
{
	protected function __construct()
	{
		parent::__construct();
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

		$this->getUltimoMensajePublico();
	}

	// MARK: GET ULTIMO MENSAJE PUBLICO

	private function getUltimoMensajePublico(): void
	{
		$statement =
			"SELECT
				id_emisor,
				contenido,
				DATE_FORMAT(fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				nombre_usuario
				FROM mensajes
				LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
				WHERE id_receptor IS NULL
				AND id_grupo IS NULL
				ORDER BY fecha_envio DESC
				LIMIT 1";

		$query = $this->connection->prepare($statement);

		$query->execute();

		$ultimo_mensaje = $query->get_result()->fetch_assoc();

		$query->close();

		$this->status = 200;
		$this->message = 'Último mensaje público obtenido con éxito';
		$this->content = $ultimo_mensaje
			? $ultimo_mensaje
			: [];
		$this->sendResponse();
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
}
