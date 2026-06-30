<?php

declare(strict_types=1);

require_once __DIR__ . '/Grupo.php';

readonly class Invitacion extends Grupo
{
	protected string $ulid_contacto;
	protected string $codigo_contacto;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: INVITAR CONTACTO

	public function invitarContacto(): void
	{
		$this->setCodigo('codigo_contacto');
		$this->checkValidationErrors();

		$query =
			"SELECT ulid_usuario
			FROM usuarios
			WHERE codigo_contacto = ?";

		$contacto = $this->executeQuery(
			$query,
			's',
			[
				$this->codigo_contacto
			],
			SqlReturn::BindResult
		);

		if (!$contacto) {
			$this->status = 404;
			$this->errors->setIntegrityError('No existe ningún usuario con ese código.');
			$this->checkIntegrityErrors();
		}

		$query =
			"INSERT INTO invitaciones_directas (ulid_usuario, ulid_contacto)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$contacto
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR CONTACTO

	public function aceptarContacto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_directos (ulid_usuario, ulid_contacto)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->ulid_contacto,
				$this->session_user
			]
		);

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_contacto
			]
		);

		$query =
			"DELETE FROM invitaciones_directas
			WHERE (
				(ulid_usuario = ? AND ulid_contacto = ?) OR (ulid_usuario = ? AND ulid_contacto = ?)
			)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_user,
				$this->ulid_contacto,
				$this->ulid_contacto,
				$this->session_user
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR CONTACTO

	public function rechazarContacto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$query =
			"DELETE FROM invitaciones_directas
			WHERE ulid_usuario = ?
			AND ulid_contacto = ?";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->ulid_contacto,
				$this->session_user
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: INVITAR GRUPO

	public function invitarGrupo(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"INSERT INTO invitaciones_grupales (ulid_usuario, ulid_grupo)
		 	VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->ulid_contacto,
				$this->ulid_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR GRUPO

	public function aceptarGrupo(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo,
			]
		);

		$query =
			"DELETE FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo,
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR GRUPO

	public function rechazarGrupo(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$query =
			"DELETE FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo,
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: READ INVITACIONES

	private function readInvitaciones(): array
	{
		$query =
			"SELECT *
				FROM (
					SELECT
						u.ulid_usuario AS id,
						u.nombre_usuario AS nombre,
						'usuario' AS tipo
					FROM usuarios u

					LEFT JOIN invitaciones_directas
						ON u.ulid_usuario = invitaciones_directas.ulid_usuario
					WHERE invitaciones_directas.ulid_contacto = ?

					GROUP BY
						u.ulid_usuario,
						u.nombre_usuario

					UNION ALL

					SELECT
						g.ulid_grupo AS id,
						g.nombre_grupo AS nombre,
						'grupo' AS tipo
					FROM grupos g

					LEFT JOIN invitaciones_grupales
						ON invitaciones_grupales.ulid_grupo = g.ulid_grupo
					WHERE invitaciones_grupales.ulid_usuario = ?

					GROUP BY
						g.ulid_grupo,
						g.nombre_grupo
				) AS invitaciones";
		// ORDER BY
		// 	(fecha_envio IS NULL) ASC,
		// 	fecha_envio DESC,
		// 	nombre ASC";

		$invitaciones = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $invitaciones;
	}

	// MARK: STREAM INVITACIONES

	protected function streamInvitacionesLogic(): void
	{
		static $invitaciones = [];

		$invitacionesUpdate = $this->readInvitaciones();

		if ($invitacionesUpdate !== $invitaciones) {
			$this->sendEvent('invitacion', $invitacionesUpdate);
			$invitaciones = $invitacionesUpdate;
		}
	}

	public function streamInvitaciones(): void
	{
		$this->setSSE([$this, "streamInvitacionesLogic"]);
	}

	// MARK: READ CONTACTOS INVITABLES

	private function readContactosInvitables(): array
	{
		$query =
			"SELECT ulid_usuario, nombre_usuario
			FROM usuarios
			WHERE ulid_usuario NOT IN
			(
				SELECT ulid_usuario
				FROM contactos_grupales
				WHERE ulid_grupo = ?
			)
			AND ulid_usuario NOT IN
			(
				SELECT ulid_usuario
				FROM invitaciones_grupales
				WHERE ulid_grupo = ?
			)
			AND ulid_usuario IN
			(
				SELECT ulid_contacto
				FROM contactos_directos
				WHERE ulid_usuario = ?
			)
			ORDER BY nombre_usuario ASC";

		$contactosInvitables = $this->executeQuery(
			$query,
			'sss',
			[
				$this->ulid_grupo,
				$this->ulid_grupo,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $contactosInvitables;
	}

	// MARK: STREAM CONTACTOS INVITABLES

	protected function streamContactosInvitablesLogic(): void
	{
		static $contactosInvitables = [];

		$contactosInvitablesUpdate = $this->readContactosInvitables();

		if ($contactosInvitablesUpdate !== $contactosInvitables) {
			$this->sendEvent('no miembro', $contactosInvitablesUpdate);
			$contactosInvitables = $contactosInvitablesUpdate;
		}
	}

	public function streamContactosInvitables(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();
		$this->isMiembroGrupo();

		$this->setSSE([$this, "streamContactosInvitablesLogic"]);
	}
}
