<?php

declare(strict_types=1);

require_once __DIR__ . '/Usuario.php';

readonly class Grupo extends Usuario
{
	protected string $ulid_grupo;
	protected string $nombre_grupo;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: IS FUNDADOR

	protected function isFundadorGrupo(): void
	{
		$query =
			"SELECT ulid_fundador
			FROM grupos
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$ulid_fundador = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo
			],
			SqlReturn::BindResult
		);

		if ($ulid_fundador !== $this->session_user) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el fundador del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	protected function isMiembroGrupo(): void
	{
		$query =
			"SELECT 1
			FROM contactos_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$rol = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo
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

		$ulid = $this->generateUlid();

		$query =
			"INSERT INTO grupos (ulid_grupo, nombre_grupo, ulid_fundador)
		 	VALUES (?, ?, ?)";

		try {
			$this->executeQuery(
				$query,
				'sss',
				[
					$ulid,
					$this->nombre_grupo,
					$this->session_user
				]
			);
		} catch (\mysqli_sql_exception $error) {

			if ($error->getCode() === 1062) {
				$this->status = 409;
				$this->errors->setIntegrityError('¡Este nombre de grupo ya existe!');
				$this->checkIntegrityErrors();
			}
		}

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
		 	VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$ulid,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ABANDONAR GRUPO

	public function abandonarGrupo(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"DELETE FROM contactos_grupales
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

	// MARK: DELETE GRUPO

	public function deleteGrupo(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->isFundadorGrupo();

		$query =
			"DELETE FROM grupos
			WHERE ulid_grupo = ?";

		$this->executeQuery(
			$query,
			's',
			[
				$this->ulid_grupo
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}
}
