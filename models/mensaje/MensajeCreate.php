<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajePermissions.php';

readonly class MensajeCreate extends MensajePermissions
{
	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->createMensajeDirecto();
		}

		if (isset($_POST['id_grupo'])) {
			$this->createMensajeGrupal();
		}

		$this->createMensajePublico();
	}

	// MARK: CREATE MENSAJE PUBLICO

	public function createMensajePublico(): void
	{
		$this->setContenido();

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
		$contenido = $this->contenido;

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor)
			VALUES (?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"si",
			$contenido,
			$id_emisor
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje público creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setContenido();
		$this->setId('id_receptor');

		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_emisor = $this->session_user;
		$contenido = $this->contenido;

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_receptor)
			VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_receptor
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje directo creado con éxito";
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->setContenido();
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$id_emisor = $this->session_user;
		$contenido = $this->contenido;
		$id_grupo = $this->id_grupo;

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, id_grupo)
			VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$contenido,
			$id_emisor,
			$id_grupo
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->message = "Mensaje grupal creado con éxito";
		$this->sendResponse();
	}

	public function deleteMensaje(): void
	{
		$this->isAutorMensaje();

		$id_mensaje = $this->id_mensaje;
		$id_emisor = $this->session_user;

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_mensaje,
			$id_emisor
		);

		$query->execute();
		$num_filas = $query->affected_rows;
		$query->close();

		if ($num_filas === 1) {
			$this->status = 204;
		} else {
			$this->status = 404;
			$this->message = '¡El mensaje solicitado no existe!';
		}
		$this->sendResponse();
	}
}
