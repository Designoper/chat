<?php

declare(strict_types=1);

require_once __DIR__ . '/MensajeBase.php';

readonly class MensajePermissions extends MensajeBase
{
	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: IS AUTOR MENSAJE

	protected function isAutorMensaje(): void
	{
		$this->setId('id_mensaje');

		$this->checkValidationErrors();

		$id_usuario = $this->session_user;
		$id_mensaje = $this->id_mensaje;

		$statement =
			"SELECT id_emisor
			FROM mensajes
			WHERE id_mensaje = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_mensaje
		);

		$query->execute();
		$query->bind_result($autor);
		$query->fetch();
		$query->close();

		if ($autor !== $id_usuario) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	protected function isMiembroGrupo(): void
	{
		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();
		$query->bind_result($rol);
		$query->fetch();
		$query->close();

		if ($rol === 'miembro' || $rol === 'fundador') {
			return;
		}

		$this->status = 403;
		$this->errors->setIntegrityError('No formas parte del grupo');
		$this->checkIntegrityErrors();
	}
}
