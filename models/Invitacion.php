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

	// ============================================================================
	// MARK: INVITAR CONTACTO
	// ============================================================================
	public function invitarContacto(): void
	{
		$this->setProperties([fn() => $this->setCodigo('codigo_contacto')]);

		$query =
			"SELECT ulid_usuario
			FROM usuarios
			WHERE codigo_contacto = ?";

		$params = [['s', $this->codigo_contacto]];

		$contacto = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

		if (!$contacto) {
			$this->integrityErrorSetup(404, "No existe ningún usuario con ese código.");
		}

		if ($contacto === $this->session_ulid) {
			$this->integrityErrorSetup(409, "No puedes invitarte a ti mismo.");
		}

		// Ya son contactos
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_directos
				WHERE ulid_min = LEAST(?, ?)
				AND ulid_max = GREATEST(?, ?)
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $contacto],
			['s', $this->session_ulid],
			['s', $contacto]
		];

		$yaSonContactos = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($yaSonContactos) {
			$this->integrityErrorSetup(409, "Ya sois contactos.");
		}

		// Invitación duplicada
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM invitaciones_directas
				WHERE ulid_usuario = ?
				AND ulid_contacto = ?
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $contacto]
		];

		$yaInvitado = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($yaInvitado) {
			$this->integrityErrorSetup(409, "Ya has enviado una invitación a este usuario.");
		}

		// Invitación cruzada → aceptar automáticamente
		$params = [
			['s', $contacto],
			['s', $this->session_ulid]
		];

		$invitacionCruzada = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($invitacionCruzada) {

			$ulid_min = min($this->session_ulid, $contacto);
			$ulid_max = max($this->session_ulid, $contacto);

			$query =
				"INSERT IGNORE INTO contactos_directos (ulid_min, ulid_max)
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
			['s', $contacto]
		];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(201);
	}

	// ============================================================================
	// MARK: IS ACEPTAR CONTACTO
	// ============================================================================
	public function aceptarContacto(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_contacto')]);

		$ulid_min = min($this->ulid_contacto, $this->session_ulid);
		$ulid_max = max($this->ulid_contacto, $this->session_ulid);

		$query =
			"INSERT IGNORE INTO contactos_directos (ulid_min, ulid_max)
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

	// ============================================================================
	// MARK: RECHAZAR CONTACTO
	// ============================================================================
	public function rechazarContacto(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_contacto')]);

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

	// ============================================================================
	// MARK: INVITAR GRUPO
	// ============================================================================
	public function invitarGrupo(): void
	{
		$this->setProperties([
			fn() => $this->setUlid('ulid_contacto'),
			fn() => $this->setUlid('ulid_grupo')
		]);

		// 3. Validar que el grupo existe
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM grupos
				WHERE ulid_grupo = ?
			)";

		$params = [['s', $this->ulid_grupo]];

		$existeGrupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$existeGrupo) {
			$this->integrityErrorSetup(404, "El grupo no existe.");
		}

		$this->isMiembroGrupo();

		// 1. No puedes invitarte a ti mismo
		if ($this->session_ulid === $this->ulid_contacto) {
			$this->integrityErrorSetup(409, "No puedes invitarte a ti mismo.");
		}

		// 7. Validar que es tu contacto directo
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_directos
				WHERE ulid_min = LEAST(?, ?)
				AND ulid_max = GREATEST(?, ?)
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_contacto],
			['s', $this->session_ulid],
			['s', $this->ulid_contacto]
		];

		$esContacto = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$esContacto) {
			$this->integrityErrorSetup(403, "Solo puedes invitar a tus contactos directos.");
		}

		// 2. Validar que el usuario existe
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM usuarios
				WHERE ulid_usuario = ?
			)";

		$params = [['s', $this->ulid_contacto]];

		$usuario = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$usuario) {
			$this->integrityErrorSetup(404, "El usuario no existe.");
		}

		// 5. Validar que el invitado NO es miembro del grupo
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->ulid_contacto],
			['s', $this->ulid_grupo]
		];

		$esMiembro = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($esMiembro) {
			$this->integrityErrorSetup(409, "Este usuario ya es miembro del grupo.");
		}

		// 6. Validar que NO tiene invitación pendiente
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM invitaciones_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->ulid_contacto],
			['s', $this->ulid_grupo]
		];

		$yaInvitado = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($yaInvitado) {
			$this->integrityErrorSetup(409, "Este usuario ya tiene una invitación pendiente.");
		}

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

	// ============================================================================
	// MARK: ACEPTAR GRUPO
	// ============================================================================
	public function aceptarGrupo(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

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

	// ============================================================================
	// MARK: RECHAZAR GRUPO
	// ============================================================================
	public function rechazarGrupo(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

		// 1. Validar que el grupo existe
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM grupos
				WHERE ulid_grupo = ?
			)";

		$params = [['s', $this->ulid_grupo]];

		$grupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$grupo) {
			$this->integrityErrorSetup(404, "El grupo no existe.");
		}

		// 3. Validar que NO eres miembro del grupo
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

		$miembro_grupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if ($miembro_grupo) {
			$this->integrityErrorSetup(409, "Ya eres miembro del grupo.");
		}

		// 2. Validar que existe la invitación
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM invitaciones_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo]
		];

		$invitacion = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$invitacion) {
			$this->integrityErrorSetup(404, "No tienes ninguna invitación para este grupo.");
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

	// ============================================================================
	// MARK: READ INVITACIONES
	// ============================================================================
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

	// ============================================================================
	// MARK: STREAM INVITACIONES LOGIC
	// ============================================================================
	protected function streamInvitacionesLogic(): void
	{
		static $invitaciones = [];

		$invitacionesUpdate = $this->readInvitaciones();

		if ($invitacionesUpdate !== $invitaciones) {
			$this->sendEvent('invitacion', $invitacionesUpdate);
			$invitaciones = $invitacionesUpdate;
		}
	}

	// ============================================================================
	// MARK: STREAM INVITACIONES
	// ============================================================================
	public function streamInvitaciones(): void
	{
		$this->setSSE([$this, "streamInvitacionesLogic"]);
	}

	// ============================================================================
	// MARK: READ CONTACTOS INVITABLES
	// ============================================================================
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
				WHERE cd.ulid_min = LEAST(?, u.ulid_usuario)
				AND cd.ulid_max = GREATEST(?, u.ulid_usuario)
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

	// ============================================================================
	// MARK: STREAM CONTACTOS INVITABLES LOGIC
	// ============================================================================
	protected function streamContactosInvitablesLogic(): void
	{
		static $contactosInvitables = [];

		$contactosInvitablesUpdate = $this->readContactosInvitables();

		if ($contactosInvitablesUpdate !== $contactosInvitables) {
			$this->sendEvent('no miembro', $contactosInvitablesUpdate);
			$contactosInvitables = $contactosInvitablesUpdate;
		}
	}

	// ============================================================================
	// MARK: STREAM CONTACTOS INVITABLES
	// ============================================================================
	public function streamContactosInvitables(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

		$this->isMiembroGrupo();

		$this->setSSE([$this, "streamContactosInvitablesLogic"]);
	}
}
