<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Helper.php';

enum MensajeProperties: string
{
	case ID_MENSAJE = 'id_mensaje';
	case ID_RECEPTOR = 'id_receptor';
	case ID_GRUPO = 'id_grupo';
	case ID_CONTENIDO = 'contenido';
}

readonly class Mensaje extends Helper
{
	protected int $id_mensaje;
	protected int $id_receptor;
	protected int $id_grupo;
	protected string $contenido;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: GET ULTIMO ID

	public function getUltimoIdMensaje(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->getUltimoIdDirecto();
		}

		if (isset($_GET['id_grupo'])) {
			$this->getUltimoIdGrupal();
		}
	}

	// MARK: GET ULTIMO ID DIRECTO

	private function getUltimoIdDirecto(): void
	{
		$this->setId(MensajeProperties::ID_RECEPTOR->value);
		$this->checkValidationErrors();

		$query =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0) AS id_mensaje";

		$last_id = $this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_receptor
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: GET ULTIMO ID GRUPAL

	private function getUltimoIdGrupal(): void
	{
		$this->setId(MensajeProperties::ID_GRUPO->value);
		$this->checkValidationErrors();

		$query =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0) AS id_mensaje";

		$last_id = $this->executeQuery(
			$query,
			'ii',
			[
				$this->session_user,
				$this->id_grupo
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID

	public function setultimoIdLeido(): void
	{
		if (isset($_POST[MensajeProperties::ID_RECEPTOR->value])) {
			$this->setUltimoIdDirecto();
		}

		if (isset($_POST[MensajeProperties::ID_GRUPO->value])) {
			$this->setUltimoIdGrupal();
		}
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(): void
	{
		$this->setId('id_receptor');
		$this->setId('id_mensaje');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_directos (id_usuario, id_receptor, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->executeQuery(
			$query,
			'iiii',
			[
				$this->session_user,
				$this->id_receptor,
				$this->id_mensaje,
				$this->id_mensaje
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(): void
	{
		$this->setId('id_grupo');
		$this->setId('id_mensaje');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_grupales (id_usuario, id_grupo, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->executeQuery(
			$query,
			'iiii',
			[
				$this->session_user,
				$this->id_grupo,
				$this->id_mensaje,
				$this->id_mensaje
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES

	public function readMensajes(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->readMensajesDirectos();
		}

		if (isset($_GET['id_grupo'])) {
			$this->readMensajesGrupales();
		}
	}

	// MARK: READ MENSAJES DIRECTOS

	private function readMensajesDirectos(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$user_min = min($this->session_user, $this->id_receptor);
		$user_max = max($this->session_user, $this->id_receptor);

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query = <<<SQL
			SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, $dateFormat) AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_receptor IS NOT NULL
			AND (
				LEAST(id_emisor, id_receptor) = ?
				AND GREATEST(id_emisor, id_receptor) = ?
			)
			ORDER BY fecha_envio ASC
			SQL;

		$mensajes = $this->executeQuery(
			$query,
			"ii",
			[
				$user_min,
				$user_max
			],
			SqlReturn::FetchAll
		);

		$this->status = 200;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES GRUPALES

	private function readMensajesGrupales(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, $dateFormat) AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_grupo = ?
			ORDER BY fecha_envio ASC";

		$mensajes = $this->executeQuery(
			$query,
			"i",
			[
				$this->id_grupo
			],
			SqlReturn::FetchAll
		);

		$this->status = 200;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: IS AUTOR MENSAJE

	private function isAutorMensaje(): void
	{
		$this->setId('id_mensaje');
		$this->checkValidationErrors();

		$query =
			"SELECT id_emisor
			FROM mensajes
			WHERE id_mensaje = ?";

		$autor = $this->executeQuery(
			$query,
			"i",
			[
				$this->id_mensaje
			],
			SqlReturn::BindResult
		);

		if ($autor !== $this->session_user) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	private function isMiembroGrupo(): void
	{
		$query =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->executeQuery(
			$query,
			"ii",
			[
				$this->session_user,
				$this->id_grupo
			],
			SqlReturn::BindResult
		);

		if ($rol !== 'miembro' && $rol !== 'fundador') {
			$this->status = 403;
			$this->errors->setIntegrityError('No formas parte del grupo');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: CREATE MENSAJE

	public function createMensaje(): void
	{
		$columna = null;
		$id_objetivo = null;

		if (isset($_POST['id_receptor'])) {
			$this->setId('id_receptor');
			$this->checkValidationErrors();

			$columna = 'id_receptor';
			$id_objetivo = $this->id_receptor;
		}

		if (isset($_POST['id_grupo'])) {
			$this->setId('id_grupo');
			$this->checkValidationErrors();

			$columna = 'id_grupo';
			$id_objetivo = $this->id_grupo;
		}

		if ($columna === null || $id_objetivo === null) {
			$this->errors->setValidationError("No se ha especificado un id_receptor o id_grupo.");
			$this->checkValidationErrors();
		}

		$this->setContenido('contenido');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO mensajes (contenido, id_emisor, $columna)
			VALUES (?, ?, ?)";

		$this->executeQuery(
			$query,
			'sii',
			[
				$this->contenido,
				$this->session_user,
				$id_objetivo
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$this->isAutorMensaje();

		$query =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$this->executeQuery(
			$query,
			'ii',
			[
				$this->id_mensaje,
				$this->session_user
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	private function getNuevosMensajesDirectos(): array
	{
		$user_min = min($this->session_user, $this->id_receptor);
		$user_max = max($this->session_user, $this->id_receptor);

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, $dateFormat) AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0)
			AND (
				LEAST(id_emisor, id_receptor) = ?
				AND GREATEST(id_emisor, id_receptor) = ?
			)
			AND mensajes.id_grupo IS NULL
			ORDER BY mensajes.id_mensaje ASC";

		$mensajes = $this->executeQuery(
			$query,
			"iiii",
			[
				$this->session_user,
				$this->id_receptor,
				$user_min,
				$user_max
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: GET NUEVOS MENSAJES GRUPALES

	private function getNuevosMensajesGrupales(): array
	{
		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, $dateFormat) AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.id_emisor = usuarios.id_usuario
	        WHERE mensajes.id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0)
	        AND mensajes.id_receptor IS NULL
			AND mensajes.id_grupo = ?
	        ORDER BY mensajes.id_mensaje ASC";

		$mensajes = $this->executeQuery(
			$query,
			"iii",
			[
				$this->session_user,
				$this->id_grupo,
				$this->id_grupo
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: STREAM MENSAJES

	public function streamMensajes(): void
	{
		$mensajes = null;

		if (isset($_GET['id_receptor'])) {
			$this->setId('id_receptor');
			$this->checkValidationErrors();

			$mensajes = fn() => $this->getNuevosMensajesDirectos();
		} else if (isset($_GET['id_grupo'])) {
			$this->setId('id_grupo');
			$this->checkValidationErrors();

			$this->isMiembroGrupo();
			$mensajes = fn() => $this->getNuevosMensajesGrupales();
		}

		if ($mensajes === null) {
			$this->errors->setValidationError("No se ha especificado un receptor o grupo.");
			$this->checkValidationErrors();
		}

		$this->setSSE();

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$mensajesObtenidos = $mensajes();

			if (!empty($mensajesObtenidos)) {

				foreach ($mensajesObtenidos as $m) {
					$ultimo_id = $m["id_mensaje"];
					$this->sendEvent('mensaje', $m);
				}

				$this->sendEvent('new mensaje', $ultimo_id);
			}

			$this->heartbeat();

			usleep(300000); // 0.3s
		}
	}
}
