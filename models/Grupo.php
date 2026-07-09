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

	// ============================================================================
	// MARK: IS FUNDADOR GRUPO
	// ============================================================================
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
			$this->session_ulid,
			$this->ulid_grupo
		];

		$fundador_grupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$fundador_grupo) {
			$this->integrityErrorSetup(403, "No eres el fundador del grupo");
		}
	}

	// ============================================================================
	// MARK: IS MIEMBRO GRUPO
	// ============================================================================
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
			$this->session_ulid,
			$this->ulid_grupo
		];

		$miembro_grupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$miembro_grupo) {
			$this->integrityErrorSetup(403, "No eres miembro del grupo");
		}
	}

	// ============================================================================
	// MARK: CREATE GRUPO
	// ============================================================================
	public function createGrupo(): void
	{
		$this->setProperties([fn() => $this->setNombre('nombre_grupo')]);
		$this->ulid_grupo = $this->generateUlid();

		$query =
			"INSERT INTO grupos (ulid_grupo, nombre_grupo, ulid_fundador)
			VALUES (?, ?, ?)";

		$params = [
			$this->ulid_grupo,
			$this->nombre_grupo,
			$this->session_ulid
		];

		try {
			$this->executeQuery($query, $params);
		} catch (mysqli_sql_exception $error) {
			if ($error->getCode() === 1062) {
				$this->integrityErrorSetup(409, "¡Este nombre de grupo ya existe!");
			}
		}

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
		 	VALUES (?, ?)";

		$params = [
			$this->session_ulid,
			$this->ulid_grupo
		];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(201);
	}

	// ============================================================================
	// MARK: ABANDONAR GRUPO
	// ============================================================================
	public function abandonarGrupo(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

		$this->isMiembroGrupo();

		$query =
			"DELETE FROM contactos_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$params = [
			$this->session_ulid,
			$this->ulid_grupo
		];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(204);
	}

	// ============================================================================
	// MARK: DELETE GRUPO
	// ============================================================================
	public function deleteGrupo(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

		$this->isFundadorGrupo();

		$query =
			"DELETE FROM grupos
			WHERE ulid_grupo = ?";

		$params = [$this->ulid_grupo];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(204);
	}
}
