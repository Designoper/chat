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
			"SELECT EXISTS(
				SELECT ulid_fundador
				FROM grupos
				WHERE ulid_fundador = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$ulid_fundador = $this->executeQuery($query, $params, SqlReturn::Exists);

		$this->isAuthorized($ulid_fundador, 'No eres el fundador del grupo');
	}

	// MARK: IS MIEMBRO

	protected function isMiembroGrupo(): void
	{
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$rol = $this->executeQuery($query, $params, SqlReturn::Exists);

		$this->isAuthorized($rol, 'No eres miembro del grupo');
	}

	// MARK: CREATE GRUPO

	public function createGrupo(): void
	{
		$this->setNombre('nombre_grupo');
		$this->checkValidationErrors();

		$this->ulid_grupo = $this->generateUlid();

		$query =
			"INSERT INTO grupos (ulid_grupo, nombre_grupo, ulid_fundador)
		 	VALUES (?, ?, ?)";

		$params = [
			['s', $this->ulid_grupo],
			['s', $this->nombre_grupo],
			['s', $this->session_ulid]
		];

		try {
			$this->executeQuery($query, $params);
		} catch (\mysqli_sql_exception $error) {
			$this->isConflict($error, '¡Este nombre de grupo ya existe!');
		}

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
		 	VALUES (?, ?)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$this->executeQuery($query, $params);

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

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$this->executeQuery($query, $params);

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

		$params = [['s', $this->ulid_grupo]];

		$this->executeQuery($query, $params);

		$this->status = 204;
		$this->sendResponse();
	}
}
