<?php

declare(strict_types=1);

require_once __DIR__ . '/Grupo.php';

readonly class Invitacion extends Grupo
{
	protected int $id_contacto;
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
			"SELECT id_usuario
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
			"INSERT INTO invitaciones_directas (id_usuario, id_contacto)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ii',
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
		$this->setId('id_contacto');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_directos (id_usuario, id_contacto)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->id_contacto,
				$this->session_user
			]
		);

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_contacto
			]
		);

		$query =
			"DELETE FROM invitaciones_directas
			WHERE (
				(id_usuario = ? AND id_contacto = ?) OR (id_usuario = ? AND id_contacto = ?)
			)";

		$this->executeQuery(
			$query,
			'iiii',
			[
				$this->session_user,
				$this->id_contacto,
				$this->id_contacto,
				$this->session_user
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR CONTACTO

	public function rechazarContacto(): void
	{
		$this->setId('id_contacto');
		$this->checkValidationErrors();

		$query =
			"DELETE FROM invitaciones_directas
			WHERE id_usuario = ?
			AND id_contacto = ?";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->id_contacto,
				$this->session_user
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: READ INVITACIONES CONTACTO

	private function readInvitacionesContacto(): array
	{
		$query =
			"SELECT usuarios.id_usuario, usuarios.nombre_usuario
			FROM usuarios
			LEFT JOIN invitaciones_directas
				ON usuarios.id_usuario = invitaciones_directas.id_usuario
			WHERE invitaciones_directas.id_contacto = ?
			ORDER BY usuarios.nombre_usuario ASC";

		$invitacionesContacto = $this->executeQuery(
			$query,
			'i',
			[
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $invitacionesContacto;
	}

	// MARK: STREAM INVITACIONES CONTACTO

	protected function streamInvitacionesContactoLogic(): void
	{
		static $invitacionesContacto = [];

		$invitacionesContactoUpdate = $this->readInvitacionesContacto();

		if ($invitacionesContactoUpdate !== $invitacionesContacto) {
			$this->sendEvent('usuario', $invitacionesContactoUpdate);
			$invitacionesContacto = $invitacionesContactoUpdate;
		}
	}

	public function streamInvitacionesContacto(): void
	{
		$this->setSSE([$this, "streamInvitacionesContactoLogic"]);
	}



	// MARK: READ INVITACIONES

	private function readInvitaciones(): array
	{
		$query =
			"SELECT *
				FROM (
					-- CHATS DIRECTOS (usuarios)
					SELECT
						u.id_usuario AS id,
						u.nombre_usuario AS nombre,
						'usuario' AS tipo
					FROM usuarios u

					LEFT JOIN invitaciones_directas
						ON u.id_usuario = invitaciones_directas.id_usuario
					WHERE invitaciones_directas.id_contacto = ?

					GROUP BY
						u.id_usuario,
						u.nombre_usuario

					UNION ALL

					-- CHATS GRUPALES (grupos)
					SELECT
						g.id_grupo AS id,
						g.nombre_grupo AS nombre,
						'grupo' AS tipo
					FROM grupos g

					LEFT JOIN invitaciones_grupales
						ON invitaciones_grupales.id_grupo = g.id_grupo
					WHERE invitaciones_grupales.id_usuario = ?

					GROUP BY
						g.id_grupo,
						g.nombre_grupo
				) AS invitaciones";
		// ORDER BY
		// 	(fecha_envio IS NULL) ASC,
		// 	fecha_envio DESC,
		// 	nombre ASC";

		$invitaciones = $this->executeQuery(
			$query,
			'ii',
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
		static $invitacionesGrupo = [];

		$invitacionesGrupoUpdate = $this->readInvitaciones();

		if ($invitacionesGrupoUpdate !== $invitacionesGrupo) {
			$this->sendEvent('invitacion', $invitacionesGrupoUpdate);
			$invitacionesGrupo = $invitacionesGrupoUpdate;
		}
	}

	public function streamInvitaciones(): void
	{
		$this->setSSE([$this, "streamInvitacionesLogic"]);
	}



















	// MARK: INVITAR GRUPO

	public function invitarGrupo(): void
	{
		$this->setId('id_contacto');
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"INSERT INTO invitaciones_grupales (id_usuario, id_grupo)
		 	VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->id_contacto,
				$this->id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR GRUPO

	public function aceptarGrupo(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_grupales (id_usuario, id_grupo)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo,
			]
		);

		$query =
			"DELETE FROM invitaciones_grupales
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo,
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR GRUPO

	public function rechazarGrupo(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$query =
			"DELETE FROM invitaciones_grupales
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo,
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: READ INVITACIONES GRUPO

	private function readInvitacionesGrupo(): array
	{
		$query =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN invitaciones_grupales
				ON invitaciones_grupales.id_grupo = grupos.id_grupo
			WHERE invitaciones_grupales.id_usuario = ?
			ORDER BY grupos.nombre_grupo ASC";

		$invitacionesGrupo = $this->executeQuery(
			$query,
			'i',
			[
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $invitacionesGrupo;
	}

	// MARK: READ CONTACTOS INVITABLES

	private function readContactosInvitables(): array
	{
		$query =
			"SELECT id_usuario, nombre_usuario
			FROM usuarios
			WHERE id_usuario NOT IN
			(
				SELECT id_usuario
				FROM contactos_grupales
				WHERE id_grupo = ?
			)
			AND id_usuario NOT IN
			(
				SELECT id_usuario
				FROM invitaciones_grupales
				WHERE id_grupo = ?
			)
			AND id_usuario IN
			(
				SELECT id_contacto
				FROM contactos_directos
				WHERE id_usuario = ?
			)
			ORDER BY nombre_usuario ASC";

		$contactosInvitables = $this->executeQuery(
			$query,
			'iii',
			[
				$this->id_grupo,
				$this->id_grupo,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $contactosInvitables;
	}

	// MARK: STREAM INVITACIONES GRUPO

	protected function streamInvitacionesGrupoLogic(): void
	{
		static $invitacionesGrupo = [];

		$invitacionesGrupoUpdate = $this->readInvitacionesGrupo();

		if ($invitacionesGrupoUpdate !== $invitacionesGrupo) {
			$this->sendEvent('grupo', $invitacionesGrupoUpdate);
			$invitacionesGrupo = $invitacionesGrupoUpdate;
		}
	}

	public function streamInvitacionesGrupo(): void
	{
		$this->setSSE([$this, "streamInvitacionesGrupoLogic"]);
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
		$this->setId('id_grupo');

		$this->checkValidationErrors();
		$this->isMiembroGrupo();

		$this->setSSE([$this, "streamContactosInvitablesLogic"]);
	}
}
