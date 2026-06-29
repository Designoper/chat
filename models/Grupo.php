<?php

declare(strict_types=1);

require_once __DIR__ . '/Usuario.php';

readonly class Grupo extends Usuario
{
	protected int $id_grupo;
	protected string $nombre_grupo;
	protected int $id_invitado;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: IS FUNDADOR

	protected function isFundadorGrupo(): void
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

		if ($id_fundador !== $this->session_user) {
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
}
