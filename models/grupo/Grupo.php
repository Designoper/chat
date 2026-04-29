<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Grupo extends ApiResponse
{
	private readonly int $id_grupo;
	private readonly int $id_fundador;
	private readonly int $id_usuario;
	private readonly string $nombre_grupo;

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

		if (empty($value)) {
			$this->setValidationError("El campo $name no puede estar vacío.");
			return;
		}

		$this->nombre_grupo = $value;
	}

	private function setIdGrupo(): void
	{
		$name = 'id_grupo';
		$value = $_POST[$name] ?? null;
		$min = 1;

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
			$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
			return;
		}

		$this->id_grupo = (int) $value;
	}

	private function setIdUsuario(): void
	{
		$name = 'id_usuario';
		$value = $_POST[$name] ?? null;
		$min = 1;

		if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
			$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
			return;
		}

		$this->id_usuario = (int) $value;
	}

	// public function invitar(): void
	// {
	// 	$name = 'id_fundador';
	// 	$value = $_POST[$name] ?? null;
	// 	$min = 1;

	// 	if (!filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))) {
	// 		$this->setValidationError("El campo $name debe ser un número entero superior o igual a $min y solo contener números.");
	// 		return;
	// 	}

	// 	$this->id_fundador = (int) $value;
	// }

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

	// MARK: CREAR GRUPO

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
		$this->setIdGrupo();
		$this->setIdUsuario();

		$this->checkValidationErrors();

		$id_fundador = $this->getAuthenticatedUserId();
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
		$this->setIdGrupo();
		$this->setIdUsuario();

		$this->checkValidationErrors();

		$id_usuario = $this->id_usuario;
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
