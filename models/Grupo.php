<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final class Grupo extends MysqliConnect
{
	private readonly int $id_grupo;
	private readonly string $nombre_grupo;
	private readonly int $id_fundador;
	private readonly int $id_usuario;

	public function __construct()
	{
		parent::__construct();

		$this->id_fundador = $this->getAuthenticatedUserId();
	}

	// MARK: SETTERS

	private function setNombreGrupo(): void
	{
		$name = 'nombre_grupo';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->setValidationError($error_message)
			: $this->nombre_grupo = $value;
	}

	private function setIdGrupo(string $method): void
	{
		$method = match ($method) {
			'$_GET' => $_GET,
			'$_POST' => $_POST,
		};

		$name = 'id_grupo';
		$value = $method[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_grupo = (int) $value
			: $this->setValidationError($error_message);
	}

	private function setIdUsuario(): void
	{
		$name = 'id_usuario';
		$value = $_POST[$name] ?? null;
		$min_range = 1;
		$error_message = "El campo $name debe ser un número entero superior o igual a $min_range y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min_range)))
			? $this->id_usuario = (int) $value
			: $this->setValidationError($error_message);
	}

	// MARK: READ GRUPOS

	public function readGrupos(): void
	{
		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo, membresias.id_usuario, membresias.rol, usuarios.nombre_usuario
			FROM grupos
			LEFT JOIN membresias on membresias.id_grupo = grupos.id_grupo
			LEFT JOIN usuarios on membresias.id_usuario = usuarios.id_usuario
			ORDER BY grupos.nombre_grupo ASC";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$grupos
			? 'Grupos obtenidos.'
			: 'No hay ningún grupo.';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($grupos);
		$this->getResponse();
	}

	// MARK: READ GRUPOS MIEMBRO

	public function readGruposMiembro(): void
	{
		$id_usuario = $this->id_fundador;
		$rolFundador = 'fundador';
		$rolMiembro = 'miembro';

		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN membresias on membresias.id_grupo = grupos.id_grupo
			WHERE membresias.id_usuario = ?
			AND (membresias.rol = ? OR membresias.rol = ?)
			ORDER BY grupos.nombre_grupo ASC";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"iss",
			$id_usuario,
			$rolFundador,
			$rolMiembro
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$grupos
			? 'Grupos obtenidos.'
			: 'No hay ningún grupo.';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($grupos);
		$this->getResponse();
	}

	// MARK: READ GRUPOS PENDIENTE

	public function readGruposPendiente(): void
	{
		$id_usuario = $this->id_fundador;
		$rolPendiente = 'pendiente';

		$statement =
			"SELECT grupos.id_grupo, grupos.nombre_grupo
			FROM grupos
			LEFT JOIN membresias on membresias.id_grupo = grupos.id_grupo
			WHERE membresias.id_usuario = ?
			AND membresias.rol = ?
			ORDER BY grupos.nombre_grupo ASC";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"is",
			$id_usuario,
			$rolPendiente,
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$grupos
			? 'Grupos obtenidos.'
			: 'No tienes ninguna invitación pendiente.';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($grupos);
		$this->getResponse();
	}

	// MARK: READ GRUPOS NO MIEMBRO

	public function readGruposNoMiembro(): void
	{
		$this->setIdGrupo('$_GET');
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT id_usuario, nombre_usuario
			FROM usuarios
			WHERE id_usuario NOT IN (
				SELECT id_usuario
				FROM membresias
				WHERE id_grupo = ?
			);";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$id_grupo,
		);

		$query->execute();
		$grupos = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$grupos
			? 'No miembros obtenidos.'
			: 'Cambiar.';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($grupos);
		$this->getResponse();
	}

	// MARK: IS AUTOR GRUPO

	public function isAutorGrupo(): void
	{
		$this->setIdGrupo('$_POST');
		$this->checkValidationErrors();

		$id_usuario = $this->id_fundador;
		$id_grupo = $this->id_grupo;
		$rolFundador = 'fundador';

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"ii",
			$id_usuario,
			$id_grupo
		);

		$query->execute();

		$autor = $query->get_result()->fetch_assoc();
		$query->close();

		if ($autor['rol'] !== $rolFundador) {
			$this->setStatus(403);
			$this->setIntegrityError('No eres el fundador del grupo');
			$this->checkIntegrityErrors();
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

		$query = $this->getConnection()->prepare($statement);

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

		$query = $this->getConnection()->prepare($statement2);

		$query->bind_param(
			"iis",
			$id_fundador,
			$id_grupo,
			$rol
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Grupo creado con éxito");
		$this->getResponse();
	}

	// MARK: INVITAR

	public function invitar(): void
	{
		$this->setIdGrupo('$_POST');
		$this->setIdUsuario();

		$this->checkValidationErrors();

		$id_usuario = $this->id_usuario;
		$id_grupo = $this->id_grupo;
		$rol = 'pendiente';

		$statement =
			"INSERT INTO membresias (id_usuario, id_grupo, rol)
		 VALUES (?, ?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"iis",
			$id_usuario,
			$id_grupo,
			$rol
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Invitación creada con éxito");
		$this->getResponse();
	}

	// MARK: ACEPTAR INVITACIÓN

	public function aceptarInvitacion(): void
	{
		$this->setIdGrupo('$_POST');

		$this->checkValidationErrors();

		$id_usuario = $this->id_fundador;
		$id_grupo = $this->id_grupo;
		$rol = 'miembro';

		$statement =
			"UPDATE membresias
			SET rol = ?
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"sii",
			$rol,
			$id_usuario,
			$id_grupo
		);

		$query->execute();
		$query->close();

		$this->setStatus(200);
		$this->setMessage("Invitación aceptada con éxito");
		$this->getResponse();
	}
}
