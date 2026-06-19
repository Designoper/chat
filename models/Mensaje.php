<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Helper.php';

readonly class Mensaje extends Helper
{
	protected int $id_mensaje;
	protected string $contenido;
	protected int $id_receptor;
	protected int $id_grupo;

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
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$statement =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0) AS id_mensaje";

		$last_id = $this->sqlAll(
			$statement,
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
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$statement =
			"SELECT COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0) AS id_mensaje";

		$last_id = $this->sqlAll(
			$statement,
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
		if (isset($_POST['id_receptor'])) {
			$this->setUltimoIdDirecto();
		}

		if (isset($_POST['id_grupo'])) {
			$this->setUltimoIdGrupal();
		}
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(): void
	{
		$this->setId('id_receptor');
		$this->setId('id_mensaje');
		$this->checkValidationErrors();

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_directos (id_usuario, id_receptor, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->sqlAll(
			$statement,
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

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_grupales (id_usuario, id_grupo, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->sqlAll(
			$statement,
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

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$statement = <<<SQL
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
				(id_emisor = ? AND id_receptor = ?)
				OR (id_emisor = ? AND id_receptor = ?)
			)
			ORDER BY fecha_envio ASC
			SQL;

		$mensajes = $this->sqlAll(
			$statement,
			"iiii",
			[
				$this->session_user,
				$this->id_receptor,
				$this->id_receptor,
				$this->session_user,
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

		$statement =
			"SELECT
				mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
				mensajes.id_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.id_emisor = usuarios.id_usuario
			WHERE mensajes.id_grupo = ?
			ORDER BY fecha_envio ASC";

		$mensajes = $this->sqlAll(
			$statement,
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

		$statement =
			"SELECT id_emisor
			FROM mensajes
			WHERE id_mensaje = ?";

		$autor = $this->sqlAll(
			$statement,
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
		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->sqlAll(
			$statement,
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

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, $columna)
			VALUES (?, ?, ?)";

		$this->sqlAll(
			$statement,
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

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$this->sqlAll(
			$statement,
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
		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
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
				(id_emisor = ? AND id_receptor = ?)
				OR (id_emisor = ? AND id_receptor = ?)
			)
			AND mensajes.id_grupo IS NULL
			ORDER BY mensajes.id_mensaje ASC";

		$mensajes = $this->sqlAll(
			$statement,
			"iiiiii",
			[
				$this->session_user,
				$this->id_receptor,
				$this->session_user,
				$this->id_receptor,
				$this->id_receptor,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: GET NUEVOS MENSAJES GRUPALES

	private function getNuevosMensajesGrupales(): array
	{
		$statement =
			"SELECT mensajes.id_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
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

		$mensajes = $this->sqlAll(
			$statement,
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
