<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Grupo extends MysqliConnect
{
	private int $id_grupo;
	private string $nombre_grupo;
	private int $id_fundador;
	private int $id_usuario;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
		$this->id_fundador = $this->session_user;
	}

	// MARK: SETTERS

	private function setNombreGrupo(): void
	{
		$name = 'nombre_grupo';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->nombre_grupo = $value;
	}

	private function setIdGrupo(): void
	{
		$name = 'id_grupo';
		$value = $_REQUEST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_grupo = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	private function setIdUsuario(): void
	{
		$name = 'id_usuario';
		$value = $_POST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_usuario = (int) $value
			: $this->errors->setValidationError($error_message);
	}

	// MARK: READ GRUPOS MIEMBRO

	public function readGruposMiembro(): void
	{
		$id_usuario = $this->id_fundador;

		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN membresias
				ON membresias.id_grupo = grupos.id_grupo
			WHERE membresias.id_usuario = ?
			AND membresias.rol IN ('fundador','miembro')
			ORDER BY grupos.nombre_grupo ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$this->status = 200;
		$this->content = $grupos;
		$this->sendResponse();
	}

	// MARK: OBTAIN GRUPOS PENDIENTE

	private function obtainGruposPendiente(): array
	{
		$id_usuario = $this->id_fundador;
		$rolPendiente = 'pendiente';

		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN membresias
				ON membresias.id_grupo = grupos.id_grupo
			WHERE membresias.id_usuario = ?
			AND membresias.rol = ?
			ORDER BY grupos.nombre_grupo ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"is",
			$id_usuario,
			$rolPendiente,
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		return $grupos;
	}

	// MARK: READ GRUPOS PENDIENTE

	public function readGruposPendiente(): void
	{
		$grupos = $this->obtainGruposPendiente();

		$this->status = 200;
		$this->content = $grupos;
		$this->sendResponse();
	}

	// MARK: READ GRUPOS NO MIEMBRO

	public function readGruposNoMiembro(): void
	{
		$this->setIdGrupo();
		$this->checkValidationErrors();

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

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_grupo,
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$this->status = 200;
		$this->content = $grupos;
		$this->sendResponse();
	}

	// MARK: IS AUTOR GRUPO

	public function isAutorGrupo(): void
	{
		$this->setIdGrupo();
		$this->checkValidationErrors();

		$id_usuario = $this->id_fundador;
		$id_grupo = $this->id_grupo;
		$rolFundador = 'fundador';

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

		$autor = $query->get_result()->fetch_assoc();
		$query->close();

		if ($autor['rol'] !== $rolFundador) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el fundador del grupo');
		}
	}

	// MARK: CREATE GRUPO

	public function createGrupo(): void
	{
		$this->setNombreGrupo();

		$this->checkValidationErrors();

		$nombre_grupo = $this->nombre_grupo;

		$statement =
			"INSERT INTO grupos (nombre_grupo)
		 	VALUES (?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"s",
			$nombre_grupo
		);

		$query->execute();

		$id_grupo = $query->insert_id;
		$query->close();

		$id_fundador = $this->id_fundador;
		$rol = 'fundador';



		$statement2 =
			"INSERT INTO membresias (id_usuario, id_grupo, rol)
		 	VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement2);

		$query->bind_param(
			"iis",
			$id_fundador,
			$id_grupo,
			$rol
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: INVITAR

	public function invitar(): void
	{
		$this->setIdGrupo();
		$this->setIdUsuario();

		$this->checkValidationErrors();

		$id_usuario = $this->id_usuario;
		$id_grupo = $this->id_grupo;
		$rol = 'pendiente';

		$statement =
			"INSERT INTO membresias (id_usuario, id_grupo, rol)
		 VALUES (?, ?, ?)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iis",
			$id_usuario,
			$id_grupo,
			$rol
		);

		$query->execute();
		$query->close();

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR INVITACIÓN

	public function aceptarInvitacion(): void
	{
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_usuario = $this->id_fundador;
		$id_grupo = $this->id_grupo;
		$rol = 'miembro';

		$statement =
			"UPDATE membresias
			SET rol = ?
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sii",
			$rol,
			$id_usuario,
			$id_grupo
		);

		$query->execute();
		$query->close();

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR INVITACIÓN

	public function rechazarInvitacion(): void
	{
		$this->setIdGrupo();

		$this->checkValidationErrors();

		$id_usuario = $this->id_fundador;
		$id_grupo = $this->id_grupo;

		$statement =
			"DELETE FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?
			AND rol = 'pendiente'";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();
		$query->close();

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: STREAM GRUPOS

	public function streamGrupos(): void
	{
		$this->start();

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
}
