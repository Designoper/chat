<?php

declare(strict_types=1);

require_once __DIR__ . '/Grupo.php';

readonly class Invitacion extends Grupo
{
	protected string $ulid_contacto;
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

		// Buscar usuario por código
		$query =
			"SELECT ulid_usuario
			FROM usuarios
			WHERE codigo_contacto = ?";

		$params = [['s', $this->session_ulid]];

		$contacto = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$contacto) {
			$this->status = 404;
			$this->errors->setIntegrityError('No existe ningún usuario con ese código.');
			$this->checkIntegrityErrors();
		}

		if ($contacto === $this->session_ulid) {
			$this->status = 409;
			$this->errors->setIntegrityError('No puedes invitarte a ti mismo.');
			$this->checkIntegrityErrors();
		}

		// Ya son contactos
		$query =
			"SELECT 1
			FROM contactos_directos
			WHERE ulid_a = LEAST(?, ?)
			AND ulid_b = GREATEST(?, ?)";

		$params = [
			['s', $this->session_ulid],
			['s', $contacto],
			['s', $this->session_ulid],
			['s', $contacto]
		];

		$yaSonContactos = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($yaSonContactos) {
			$this->status = 409;
			$this->errors->setIntegrityError('Ya sois contactos.');
			$this->checkIntegrityErrors();
		}

		// Invitación duplicada
		$query =
			"SELECT 1
			FROM invitaciones_directas
			WHERE ulid_usuario = ?
			AND ulid_contacto = ?";

		$params = [
			['s', $this->session_ulid],
			['s', $contacto]
		];

		$yaInvitado = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($yaInvitado) {
			$this->status = 409;
			$this->errors->setIntegrityError('Ya has enviado una invitación a este usuario.');
			$this->checkIntegrityErrors();
		}

		// Invitación cruzada → aceptar automáticamente
		$query =
			"SELECT 1
			FROM invitaciones_directas
			WHERE ulid_usuario = ?
			AND ulid_contacto = ?";

		$params = [
			['s', $contacto],
			['s', $this->session_ulid]
		];

		$invitacionCruzada = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($invitacionCruzada) {

			$ulid_min = min($this->session_ulid, $contacto);
			$ulid_max = max($this->session_ulid, $contacto);

			$query =
				"INSERT IGNORE INTO contactos_directos (ulid_a, ulid_b)
             	VALUES (?, ?)";

			$params = [
				['s', $ulid_min],
				['s', $ulid_max]
			];

			$this->executeQuery($query, $params);

			// Eliminar invitaciones cruzadas
			$query =
				"DELETE FROM invitaciones_directas
				WHERE (ulid_usuario, ulid_contacto) IN
				(
					(?, ?),
					(?, ?)
				)";

			$params = [
				['s', $this->session_ulid],
				['s', $this->ulid_contacto],
				['s', $this->ulid_contacto],
				['s', $this->session_ulid]
			];

			$this->executeQuery($query, $params);

			$this->sendOkResponse(201);
		}

		// Si no hay cruzada → crear invitación normal
		$query =
			"INSERT INTO invitaciones_directas (ulid_usuario, ulid_contacto)
         	VALUES (?, ?)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_contacto]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(201);
	}

	// MARK: ACEPTAR CONTACTO

	public function aceptarContacto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$ulid_min = min($this->ulid_contacto, $this->session_ulid);
		$ulid_max = max($this->ulid_contacto, $this->session_ulid);

		$query =
			"INSERT IGNORE INTO contactos_directos (ulid_a, ulid_b)
         	VALUES (?, ?)";

		$params = [
			['s', $ulid_min],
			['s', $ulid_max]
		];

		$this->executeQuery($query, $params);

		$query =
			"DELETE FROM invitaciones_directas
         	WHERE (ulid_usuario, ulid_contacto) IN
			(
				(?, ?),
				(?, ?)
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_contacto],
			['s', $this->ulid_contacto],
			['s', $this->session_ulid]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(200);
	}

	// MARK: RECHAZAR CONTACTO

	public function rechazarContacto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$query =
			"DELETE FROM invitaciones_directas
			WHERE ulid_usuario = ?
			AND ulid_contacto = ?";

		$params = [
			['s', $this->ulid_contacto],
			['s', $this->session_ulid]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(204);
	}

	// MARK: INVITAR GRUPO

	public function invitarGrupo(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		// 1. No puedes invitarte a ti mismo
		if ($this->session_ulid === $this->ulid_contacto) {
			$this->status = 409;
			$this->errors->setIntegrityError('No puedes invitarte a ti mismo.');
			$this->checkIntegrityErrors();
		}

		// 2. Validar que el usuario existe
		$query =
			"SELECT 1
			FROM usuarios
			WHERE ulid_usuario = ?";

		$params = [['s', $this->ulid_contacto]];

		$existeUsuario = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$existeUsuario) {
			$this->status = 404;
			$this->errors->setIntegrityError('El usuario no existe.');
			$this->checkIntegrityErrors();
		}

		// 3. Validar que el grupo existe
		$query =
			"SELECT 1
			FROM grupos
			WHERE ulid_grupo = ?";

		$params = [['s', $this->ulid_grupo]];

		$existeGrupo = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$existeGrupo) {
			$this->status = 404;
			$this->errors->setIntegrityError('El grupo no existe.');
			$this->checkIntegrityErrors();
		}

		$this->isMiembroGrupo();

		// 5. Validar que el invitado NO es miembro del grupo
		$query =
			"SELECT 1
         	FROM contactos_grupales
         	WHERE ulid_usuario = ?
          	AND ulid_grupo = ?";

		$params = [
			['s', $this->ulid_contacto],
			['s', $this->ulid_grupo]
		];

		$esMiembro = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($esMiembro) {
			$this->status = 409;
			$this->errors->setIntegrityError('Este usuario ya es miembro del grupo.');
			$this->checkIntegrityErrors();
		}

		// 6. Validar que NO tiene invitación pendiente
		$query =
			"SELECT 1
			FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$params = [
			['s', $this->ulid_contacto],
			['s', $this->ulid_grupo]
		];

		$yaInvitado = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($yaInvitado) {
			$this->status = 409;
			$this->errors->setIntegrityError('Este usuario ya tiene una invitación pendiente.');
			$this->checkIntegrityErrors();
		}

		// 7. Validar que es tu contacto directo
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_directos
				WHERE ulid_a = LEAST(?, ?)
				AND ulid_b = GREATEST(?, ?)
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_contacto],
			['s', $this->session_ulid],
			['s', $this->ulid_contacto]
		];

		$esContacto = $this->executeQuery($query, $params, SqlReturn::Exists);

		$this->isAuthorized($esContacto, 'Solo puedes invitar a tus contactos directos.');

		// 9. Insertar invitación
		$query =
			"INSERT INTO invitaciones_grupales (ulid_usuario, ulid_grupo)
			VALUES (?, ?)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(201);
	}

	// MARK: ACEPTAR GRUPO

	public function aceptarGrupo(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
			VALUES (?, ?)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$this->executeQuery($query, $params);

		$query =
			"DELETE FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$this->executeQuery($query, $params);

		$this->sendOkResponse(200);
	}

	// MARK: RECHAZAR GRUPO

	public function rechazarGrupo(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->ulid_grupo;

		// 1. Validar que el grupo existe
		$query =
			"SELECT 1
			FROM grupos
			WHERE ulid_grupo = ?";

		$params = [['s', $this->ulid_grupo]];

		$existeGrupo = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$existeGrupo) {
			$this->status = 404;
			$this->errors->setIntegrityError('El grupo no existe.');
			$this->checkIntegrityErrors();
		}

		// 2. Validar que existe la invitación
		$query =
			"SELECT 1
			FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$existeInvitacion = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$existeInvitacion) {
			$this->status = 404;
			$this->errors->setIntegrityError('No tienes ninguna invitación para este grupo.');
			$this->checkIntegrityErrors();
		}

		// 3. Validar que NO eres miembro del grupo
		$query =
			"SELECT 1
			FROM contactos_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$esMiembro = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if ($esMiembro) {
			$this->status = 409;
			$this->errors->setIntegrityError('Ya eres miembro del grupo. No puedes rechazar la invitación.');
			$this->checkIntegrityErrors();
		}

		// 4. Eliminar invitaciones (si hubiera duplicadas por error, se eliminan todas)
		$query =
			"DELETE FROM invitaciones_grupales
			WHERE ulid_usuario = ?
			AND ulid_grupo = ?";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(204);
	}

	// MARK: READ INVITACIONES

	private function readInvitaciones(): array
	{
		$query =
			"SELECT *
				FROM (
					SELECT
						u.ulid_usuario AS ulid,
						u.nombre_usuario AS nombre,
						'usuario' AS tipo
					FROM usuarios u

					LEFT JOIN invitaciones_directas
						ON u.ulid_usuario = invitaciones_directas.ulid_usuario
					WHERE invitaciones_directas.ulid_contacto = ?

					GROUP BY
						u.ulid_usuario,
						u.nombre_usuario

					UNION ALL

					SELECT
						g.ulid_grupo AS ulid,
						g.nombre_grupo AS nombre,
						'grupo' AS tipo
					FROM grupos g

					LEFT JOIN invitaciones_grupales
						ON invitaciones_grupales.ulid_grupo = g.ulid_grupo
					WHERE invitaciones_grupales.ulid_usuario = ?

					GROUP BY
						g.ulid_grupo,
						g.nombre_grupo
				) AS invitaciones";
		// ORDER BY
		// 	(fecha_creacion IS NULL) ASC,
		// 	fecha_creacion DESC,
		// 	nombre ASC";

		$params = [
			['s', $this->session_ulid],
			['s', $this->session_ulid]
		];

		$invitaciones = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		return $invitaciones;
	}

	// MARK: STREAM INVITACIONES

	protected function streamInvitacionesLogic(): void
	{
		static $invitaciones = [];

		$invitacionesUpdate = $this->readInvitaciones();

		if ($invitacionesUpdate !== $invitaciones) {
			$this->sendEvent('invitacion', $invitacionesUpdate);
			$invitaciones = $invitacionesUpdate;
		}
	}

	public function streamInvitaciones(): void
	{
		$this->setSSE([$this, "streamInvitacionesLogic"]);
	}

	// MARK: READ CONTACTOS INVITABLES

	private function readContactosInvitables(): array
	{
		$query =
			"SELECT u.ulid_usuario, u.nombre_usuario
			FROM usuarios u
			WHERE NOT EXISTS (
				SELECT 1
				FROM contactos_grupales cg
				WHERE cg.ulid_usuario = u.ulid_usuario
				AND cg.ulid_grupo = ?
			)
			AND NOT EXISTS (
				SELECT 1
				FROM invitaciones_grupales ig
				WHERE ig.ulid_usuario = u.ulid_usuario
				AND ig.ulid_grupo = ?
			)
			AND EXISTS (
				SELECT 1
				FROM contactos_directos cd
				WHERE cd.ulid_a = LEAST(?, u.ulid_usuario)
				AND cd.ulid_b = GREATEST(?, u.ulid_usuario)
			)
			ORDER BY u.nombre_usuario ASC";

		$params = [
			['s', $this->ulid_grupo],
			['s', $this->ulid_grupo],
			['s', $this->session_ulid],
			['s', $this->session_ulid]
		];

		$contactosInvitables = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		return $contactosInvitables;
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
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();
		$this->isMiembroGrupo();

		$this->setSSE([$this, "streamContactosInvitablesLogic"]);
	}
}
