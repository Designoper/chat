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

		$contacto = $this->executeQuery(
			$query,
			's',
			[$this->codigo_contacto],
			SqlReturn::BindResult
		);

		if (!$contacto) {
			$this->status = 404;
			$this->errors->setIntegrityError('No existe ningún usuario con ese código.');
			$this->checkIntegrityErrors();
		}

		if ($contacto === $this->session_user) {
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

		$yaSonContactos = $this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_user,
				$contacto,
				$this->session_user,
				$contacto
			],
			SqlReturn::BindResult
		);

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

		$yaInvitado = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$contacto
			],
			SqlReturn::BindResult
		);

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

		$invitacionCruzada = $this->executeQuery(
			$query,
			'ss',
			[
				$contacto,
				$this->session_user
			],
			SqlReturn::BindResult
		);

		if ($invitacionCruzada) {

			// Crear contacto normalizado
			$a = min($this->session_user, $contacto);
			$b = max($this->session_user, $contacto);

			$query =
				"INSERT IGNORE INTO contactos_directos (ulid_a, ulid_b)
             VALUES (?, ?)";

			$this->executeQuery(
				$query,
				'ss',
				[$a, $b]
			);

			// Eliminar invitaciones cruzadas
			$query =
				"DELETE FROM invitaciones_directas
             WHERE (ulid_usuario = ? AND ulid_contacto = ?)
                OR (ulid_usuario = ? AND ulid_contacto = ?)";

			$this->executeQuery(
				$query,
				'ssss',
				[
					$this->session_user,
					$contacto,
					$contacto,
					$this->session_user
				]
			);

			$this->status = 201;
			$this->sendResponse();
		}

		// Si no hay cruzada → crear invitación normal
		$query =
			"INSERT INTO invitaciones_directas (ulid_usuario, ulid_contacto)
         VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$contacto
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: ACEPTAR CONTACTO

	public function aceptarContacto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		// Normalización del par
		$a = min($this->ulid_contacto, $this->session_user);
		$b = max($this->ulid_contacto, $this->session_user);

		// Insertar contacto normalizado
		$query =
			"INSERT IGNORE INTO contactos_directos (ulid_a, ulid_b)
         	VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[$a, $b]
		);

		// Eliminar invitaciones en ambos sentidos
		$query =
			"DELETE FROM invitaciones_directas
         	WHERE (ulid_usuario = ? AND ulid_contacto = ?)
            OR (ulid_usuario = ? AND ulid_contacto = ?)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_user,
				$this->ulid_contacto,
				$this->ulid_contacto,
				$this->session_user
			]
		);

		$this->status = 200;
		$this->sendResponse();
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

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->ulid_contacto,
				$this->session_user
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: INVITAR GRUPO

	public function invitarGrupo(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$contacto = $this->ulid_contacto;
		$grupo    = $this->ulid_grupo;
		$usuario  = $this->session_user;

		// 1. No puedes invitarte a ti mismo
		if ($contacto === $usuario) {
			$this->status = 409;
			$this->errors->setIntegrityError('No puedes invitarte a ti mismo.');
			$this->checkIntegrityErrors();
		}

		// 2. Validar que el usuario existe
		$query = "SELECT 1 FROM usuarios WHERE ulid_usuario = ?";
		$existeUsuario = $this->executeQuery($query, 's', [$contacto], SqlReturn::BindResult);

		if (!$existeUsuario) {
			$this->status = 404;
			$this->errors->setIntegrityError('El usuario no existe.');
			$this->checkIntegrityErrors();
		}

		// 3. Validar que el grupo existe
		$query = "SELECT 1 FROM grupos WHERE ulid_grupo = ?";
		$existeGrupo = $this->executeQuery($query, 's', [$grupo], SqlReturn::BindResult);

		if (!$existeGrupo) {
			$this->status = 404;
			$this->errors->setIntegrityError('El grupo no existe.');
			$this->checkIntegrityErrors();
		}

		// 4. Validar que tú eres miembro del grupo
		$this->isMiembroGrupo();

		// 5. Validar que el invitado NO es miembro del grupo
		$query =
			"SELECT 1
         FROM contactos_grupales
         WHERE ulid_usuario = ?
           AND ulid_grupo = ?";

		$esMiembro = $this->executeQuery(
			$query,
			'ss',
			[$contacto, $grupo],
			SqlReturn::BindResult
		);

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

		$yaInvitado = $this->executeQuery(
			$query,
			'ss',
			[$contacto, $grupo],
			SqlReturn::BindResult
		);

		if ($yaInvitado) {
			$this->status = 409;
			$this->errors->setIntegrityError('Este usuario ya tiene una invitación pendiente.');
			$this->checkIntegrityErrors();
		}

		// 7. Validar que es tu contacto directo
		$query =
			"SELECT 1
         FROM contactos_directos
         WHERE ulid_a = LEAST(?, ?)
           AND ulid_b = GREATEST(?, ?)";

		$esContacto = $this->executeQuery(
			$query,
			'ssss',
			[$usuario, $contacto, $usuario, $contacto],
			SqlReturn::BindResult
		);

		if (!$esContacto) {
			$this->status = 409;
			$this->errors->setIntegrityError('Solo puedes invitar a tus contactos directos.');
			$this->checkIntegrityErrors();
		}

		// 8. Validar invitación cruzada (si el grupo invitó al usuario, no aplica)
		// En grupos NO existe invitación cruzada porque la invitación siempre es usuario → grupo.
		// Así que este caso NO se aplica aquí.

		// 9. Insertar invitación
		$query =
			"INSERT INTO invitaciones_grupales (ulid_usuario, ulid_grupo)
         VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[$contacto, $grupo]
		);

		$this->status = 201;
		$this->sendResponse();
	}


	// MARK: ACEPTAR GRUPO

	public function aceptarGrupo(): void
	{
		$this->setUlid('ulid_grupo');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO contactos_grupales (ulid_usuario, ulid_grupo)
			VALUES (?, ?)";

		$this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_grupo,
			]
		);

		$query =
			"DELETE FROM invitaciones_grupales
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

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: RECHAZAR GRUPO

	public function rechazarGrupo(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$grupo   = $this->ulid_grupo;
		$usuario = $this->session_user;

		// 1. Validar que el grupo existe
		$query = "SELECT 1 FROM grupos WHERE ulid_grupo = ?";
		$existeGrupo = $this->executeQuery($query, 's', [$grupo], SqlReturn::BindResult);

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

		$existeInvitacion = $this->executeQuery(
			$query,
			'ss',
			[$usuario, $grupo],
			SqlReturn::BindResult
		);

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

		$esMiembro = $this->executeQuery(
			$query,
			'ss',
			[$usuario, $grupo],
			SqlReturn::BindResult
		);

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

		$this->executeQuery(
			$query,
			'ss',
			[$usuario, $grupo]
		);

		$this->status = 204;
		$this->sendResponse();
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
		// 	(fecha_envio IS NULL) ASC,
		// 	fecha_envio DESC,
		// 	nombre ASC";

		$invitaciones = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

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
			"SELECT ulid_usuario, nombre_usuario
			FROM usuarios u
			WHERE u.ulid_usuario NOT IN
			(
				SELECT ulid_usuario
				FROM contactos_grupales
				WHERE ulid_grupo = ?
			)
			AND u.ulid_usuario NOT IN
			(
				SELECT ulid_usuario
				FROM invitaciones_grupales
				WHERE ulid_grupo = ?
			)
			AND EXISTS
			(
				SELECT 1
				FROM contactos_directos cd
				WHERE cd.ulid_a = LEAST(?, u.ulid_usuario)
				AND cd.ulid_b = GREATEST(?, u.ulid_usuario)
			)
			ORDER BY u.nombre_usuario ASC";

		$contactosInvitables = $this->executeQuery(
			$query,
			'ssss',
			[
				$this->ulid_grupo,
				$this->ulid_grupo,
				$this->session_user,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

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
