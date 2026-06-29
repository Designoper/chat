<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Helper.php';

final readonly class Grupo extends Helper
{
	protected int $id_grupo;
	protected string $nombre_grupo;
	protected int $id_invitado;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: OBTAIN GRUPOS PENDIENTE

	private function obtainGruposPendiente(): array
	{
		$query =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN invitaciones_grupales
				ON invitaciones_grupales.id_grupo = grupos.id_grupo
			WHERE invitaciones_grupales.id_usuario = ?
			ORDER BY grupos.nombre_grupo ASC";

		$grupos = $this->executeQuery(
			$query,
			'i',
			[
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $grupos;
	}

	// MARK: READ GRUPOS NO MIEMBRO

	private function readGruposNoMiembro(): array
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

		$grupos = $this->executeQuery(
			$query,
			'iii',
			[
				$this->id_grupo,
				$this->id_grupo,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $grupos;
	}

	// MARK: IS FUNDADOR

	private function isFundadorGrupo(): void
	{
		$query =
			"SELECT id_fundador
			FROM grupos
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$id_fundador = $this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo
			],
			SqlReturn::BindResult
		);

		if ($id_fundador !== $_SESSION['id_usuario']) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el fundador del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	private function isMiembroGrupo(): void
	{
		$query =
			"SELECT 1
			FROM contactos_grupales
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo
			],
			SqlReturn::BindResult
		);

		if (!$rol) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres miembro del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: CREATE GRUPO

	public function createGrupo(): void
	{
		$this->setNombre('nombre_grupo');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO grupos (nombre_grupo, id_fundador)
		 	VALUES (?, ?)";

		try {
			$id_grupo = $this->executeQuery(
				$query,
				'si',
				[
					$this->nombre_grupo,
					$this->session_user
				],
				SqlReturn::InsertId
			);
		} catch (\mysqli_sql_exception $error) {

			if ($error->getCode() === 1062) {
				$this->status = 409;
				$this->errors->setIntegrityError('¡Este nombre de grupo ya existe!');
				$this->checkIntegrityErrors();
			}

			throw $error;
		}

		$query2 =
			"INSERT INTO contactos_grupales (id_usuario, id_grupo)
		 	VALUES (?, ?)";

		$this->executeQuery(
			$query2,
			'ii',
			[
				$this->session_user,
				$id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: INVITAR

	public function invitar(): void
	{
		$this->setId('id_grupo');
		$this->setId('id_invitado');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"INSERT INTO invitaciones_grupales (id_usuario, id_grupo)
		 	VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->id_invitado,
				$this->id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR INVITACIÓN

	public function aceptarInvitacion(): void
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

		$query2 =
			"DELETE FROM invitaciones_grupales
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$this->executeQuery(
			$query2,
			'ii',
			[
				$this->session_user,
				$this->id_grupo,
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR INVITACIÓN

	public function rechazarInvitacion(): void
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

	// MARK: ABANDONAR GRUPO

	public function abandonarGrupo(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"DELETE FROM contactos_grupales
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

	// MARK: DELETE GRUPO

	public function deleteGrupo(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$this->isFundadorGrupo();

		$query =
			"DELETE FROM grupos
			WHERE id_grupo = ?";

		$this->executeQuery(
			$query,
			'i',
			[
				$this->id_grupo
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: STREAM GRUPOS PENDIENTE

	public function streamGrupos(): void
	{
		$this->setSSE();

		while (true) {

			if (connection_aborted()) {
				break;
			}

			static $gruposPendientes = [];

			$gruposPendientesUpdate = $this->obtainGruposPendiente();

			if ($gruposPendientesUpdate !== $gruposPendientes) {
				$this->sendEvent('grupo', $gruposPendientesUpdate);
				$gruposPendientes = $gruposPendientesUpdate;
			}

			$this->heartbeat();

			usleep(300000); // 0.3s
		}
	}

	// MARK: STREAM GRUPOS NO MIEMBRO

	public function streamGruposNoMiembro(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();
		$this->isMiembroGrupo();

		$this->setSSE();

		while (true) {

			if (connection_aborted()) {
				break;
			}

			static $noMiembros = [];

			$noMiembrosUpdate = $this->readGruposNoMiembro();

			if ($noMiembrosUpdate !== $noMiembros) {
				$this->sendEvent('no miembro', $noMiembrosUpdate);
				$noMiembros = $noMiembrosUpdate;
			}

			$this->heartbeat();

			usleep(300000); // 0.3s
		}
	}
}
