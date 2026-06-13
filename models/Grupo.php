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
		$id_usuario = $this->session_user;

		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN membresias
				ON membresias.id_grupo = grupos.id_grupo
			WHERE membresias.id_usuario = ?
			AND membresias.rol = 'pendiente'
			ORDER BY grupos.nombre_grupo ASC";

		$grupos = $this->sqlAll(
			$statement,
			'i',
			[
				$id_usuario
			],
			SqlReturn::Array
		);

		return $grupos;
	}

	// MARK: READ GRUPOS NO MIEMBRO

	private function readGruposNoMiembro(): array
	{
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT id_usuario, nombre_usuario
			FROM usuarios
			WHERE id_usuario NOT IN
			(
				SELECT id_usuario
				FROM membresias
				WHERE id_grupo = ?
			)";

		$grupos = $this->sqlAll(
			$statement,
			'i',
			[
				$id_grupo
			],
			SqlReturn::Array
		);

		return $grupos;
	}

	// MARK: IS FUNDADOR

	private function isFundadorGrupo(): void
	{
		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->sqlAll(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo
			],
			SqlReturn::Bind
		);

		if ($rol !== 'fundador') {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el fundador del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	private function isMiembroGrupo(): void
	{
		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->sqlAll(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo
			],
			SqlReturn::Bind
		);

		if ($rol !== 'fundador' && $rol !== 'miembro') {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres miembro del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: CREATE GRUPO

	public function createGrupo(): void
	{
		$this->checkAllowedvalues(['nombre_grupo'], 1);

		$this->setNombre('nombre_grupo');

		$this->checkValidationErrors();

		$nombre_grupo = $this->nombre_grupo;

		$statement =
			"INSERT INTO grupos (nombre_grupo)
		 	VALUES (?)";

		try {
			$id_grupo = $this->sqlAll(
				$statement,
				's',
				[
					$nombre_grupo
				],
				SqlReturn::Id
			);
		} catch (\mysqli_sql_exception $error) {

			if ($error->getCode() === 1062) {
				$this->status = 409;
				$this->errors->setIntegrityError('¡Este nombre de grupo ya existe!');
				$this->checkIntegrityErrors();
			}

			throw $error;
		}

		$id_fundador = $this->session_user;



		$statement2 =
			"INSERT INTO membresias (id_usuario, id_grupo, rol)
		 	VALUES (?, ?, 'fundador')";

		$this->sqlAll(
			$statement2,
			'ii',
			[
				$id_fundador,
				$id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: INVITAR

	public function invitar(): void
	{
		$this->checkAllowedvalues(['id_grupo', 'id_invitado'], 2);

		$this->setId('id_grupo');
		$this->setId('id_invitado');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$id_invitado = $this->id_invitado;
		$id_grupo = $this->id_grupo;

		$statement =
			"INSERT INTO membresias (id_usuario, id_grupo, rol)
		 	VALUES (?, ?, 'pendiente')";

		$this->sqlAll(
			$statement,
			'ii',
			[
				$id_invitado,
				$id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR INVITACIÓN

	public function aceptarInvitacion(): void
	{
		$this->checkAllowedvalues(['id_grupo'], 1);

		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"UPDATE membresias
			SET rol = 'miembro'
			WHERE id_usuario = ?
			AND id_grupo = ?
			AND rol = 'pendiente'";

		$this->sqlAll(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo,
			]
		);

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR INVITACIÓN

	public function rechazarInvitacion(): void
	{
		$this->checkAllowedvalues(['id_grupo'], 1);

		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"DELETE FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?
			AND rol = 'pendiente'";

		$this->sqlAll(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo,
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: ABANDONAR GRUPO

	public function abandonarGrupo(): void
	{
		$this->checkAllowedvalues(['id_grupo'], 1);

		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"DELETE FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$this->sqlAll(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo,
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: DELETE GRUPO

	public function deleteGrupo(): void
	{
		$this->checkAllowedvalues(['id_grupo'], 1);

		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$this->isFundadorGrupo();

		$id_grupo = $this->id_grupo;

		$statement =
			"DELETE FROM grupos
			WHERE id_grupo = ?";

		$this->sqlAll(
			$statement,
			'i',
			[
				$id_grupo
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: STREAM GRUPOS PENDIENTE

	public function streamGrupos(): void
	{
		$this->setSSE();

		$lastPing = 0;

		$gruposPendientes = [];

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$gruposPendientesUpdate = $this->obtainGruposPendiente();

			if ($gruposPendientesUpdate !== $gruposPendientes) {
				$this->sendEvent('grupo', $gruposPendientesUpdate);
				$gruposPendientes = $gruposPendientesUpdate;
			}

			if (time() - $lastPing > 10) {
				$this->keepAlive();
				$lastPing = time();
			}

			usleep(300000); // 0.3s
		}
	}

	// MARK: STREAM GRUPOS NO MIEMBRO

	public function streamGruposNoMiembro(): void
	{
		$this->checkAllowedvalues(['id_grupo'], 1);

		$this->setId('id_grupo');

		$this->checkValidationErrors();
		$this->isMiembroGrupo();

		$this->setSSE();

		$lastPing = 0;

		$noMiembros = [];

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$noMiembrosUpdate = $this->readGruposNoMiembro();

			if ($noMiembrosUpdate !== $noMiembros) {
				$this->sendEvent('no miembro', $noMiembrosUpdate);
				$noMiembros = $noMiembrosUpdate;
			}

			if (time() - $lastPing > 10) {
				$this->keepAlive();
				$lastPing = time();
			}

			usleep(300000); // 0.3s
		}
	}
}
