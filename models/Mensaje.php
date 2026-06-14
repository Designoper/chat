<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Helper.php';

readonly class Mensaje extends Helper
{
	protected int $id_mensaje;
	protected string $contenido;
	protected int $id_receptor;
	protected int $id_grupo;
	protected int $ultimo_id;

	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: GET ULTIMO MENSAJE

	public function getUltimoMensaje(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->getUltimoMensajeDirecto();
		}

		if (isset($_GET['id_grupo'])) {
			$this->getUltimoMensajeGrupal();
		}
	}

	// MARK: GET ULTIMO MENSAJE DIRECTO

	private function getUltimoMensajeDirecto(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT
				id_emisor,
				nombre_usuario,
				contenido,
				DATE_FORMAT(fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio
				FROM mensajes
				LEFT JOIN usuarios
					ON mensajes.id_emisor = usuarios.id_usuario
				WHERE
					(id_emisor = ? AND id_receptor = ?)
					OR (id_emisor = ? AND id_receptor = ?)
				AND id_grupo IS NULL
				ORDER BY fecha_envio DESC
				LIMIT 1";

		$ultimo_mensaje = $this->sqlAll(
			$statement,
			'iiii',
			[
				$id_usuario,
				$id_receptor,
				$id_receptor,
				$id_usuario
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $ultimo_mensaje;
		$this->sendResponse();
	}

	// MARK: GET ULTIMO MENSAJE GRUPAL

	private function getUltimoMensajeGrupal(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT
				id_emisor,
				nombre_usuario,
				contenido,
				DATE_FORMAT(fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio
				FROM mensajes
				LEFT JOIN usuarios ON mensajes.id_emisor = usuarios.id_usuario
				WHERE id_grupo = ?
				AND id_receptor IS NULL
				ORDER BY fecha_envio DESC
				LIMIT 1";

		$ultimo_mensaje = $this->sqlAll(
			$statement,
			'i',
			[
				$id_grupo
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $ultimo_mensaje;
		$this->sendResponse();
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

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

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
				$id_usuario,
				$id_receptor
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

		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

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
				$id_usuario,
				$id_grupo
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
		$this->setId('ultimo_id');

		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$ultimo_id = $this->ultimo_id;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_directos (id_usuario, id_receptor, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->sqlAll(
			$statement,
			'iiii',
			[
				$id_usuario,
				$id_receptor,
				$ultimo_id,
				$ultimo_id
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(): void
	{
		$this->setId('id_grupo');
		$this->setId('ultimo_id');

		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;
		$ultimo_id = $this->ultimo_id;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO ultimos_mensajes_leidos_grupales (id_usuario, id_grupo, id_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE id_mensaje = ?";

		$this->sqlAll(
			$statement,
			'iiii',
			[
				$id_usuario,
				$id_grupo,
				$ultimo_id,
				$ultimo_id
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD MESSAGES

	public function countUnreadMessages(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->countUnreadDirectMessages();
		}

		if (isset($_GET['id_grupo'])) {
			$this->countUnreadGroupMessages();
		}
	}

	// MARK: COUNT UNREAD DIRECT MESSAGES

	private function countUnreadDirectMessages(): void
	{
		$this->setId('id_receptor');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
		$id_receptor = $this->id_receptor;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE id_receptor = ?
			AND id_emisor = ?
			AND id_grupo IS NULL
			AND id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE id_usuario = ?
				AND id_receptor = ?
			), 0)";

		$result = $this->sqlAll(
			$statement,
			"iiii",
			[
				$id_emisor,
				$id_receptor,
				$id_emisor,
				$id_receptor
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $result;
		$this->sendResponse();
	}

	// MARK: COUNT UNREAD GROUP MESSAGES

	private function countUnreadGroupMessages(): void
	{
		$this->setId('id_grupo');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT COUNT(*) AS num_mensajes
			FROM mensajes
			WHERE id_grupo = ?
			AND id_emisor != ?
			AND id_receptor IS NULL
			AND id_mensaje > COALESCE((
				SELECT id_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE id_usuario = ?
				AND id_grupo = ?
			), 0)";

		$result = $this->sqlAll(
			$statement,
			"iiii",
			[
				$id_grupo,
				$id_emisor,
				$id_emisor,
				$id_grupo
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $result;
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

		$id_emisor = $this->session_user;
		$id_receptor = $this->id_receptor;

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
			WHERE mensajes.id_receptor IS NOT NULL
			AND (
				(id_emisor = ? AND id_receptor = ?)
				OR (id_emisor = ? AND id_receptor = ?)
			)
			ORDER BY fecha_envio ASC";

		$mensajes = $this->sqlAll(
			$statement,
			"iiii",
			[
				$id_emisor,
				$id_receptor,
				$id_receptor,
				$id_emisor
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

		$id_grupo = $this->id_grupo;

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
				$id_grupo
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

		$id_usuario = $this->session_user;
		$id_mensaje = $this->id_mensaje;

		$statement =
			"SELECT id_emisor
			FROM mensajes
			WHERE id_mensaje = ?";

		$autor = $this->sqlAll(
			$statement,
			"i",
			[
				$id_mensaje
			],
			SqlReturn::BindResult
		);

		if ($autor !== $id_usuario) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: IS MIEMBRO

	private function isMiembroGrupo(): void
	{
		$id_usuario = $this->session_user;
		$id_grupo = $this->id_grupo;

		$statement =
			"SELECT rol
			FROM membresias
			WHERE id_usuario = ?
			AND id_grupo = ?";

		$rol = $this->sqlAll(
			$statement,
			"ii",
			[
				$id_usuario,
				$id_grupo
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
		if (isset($_POST['id_receptor'])) {
			$this->setId('id_receptor');
			$columna = 'id_receptor';
			$id_objetivo = $this->id_receptor;
		}

		if (isset($_POST['id_grupo'])) {
			$this->setId('id_grupo');
			$columna = 'id_grupo';
			$id_objetivo = $this->id_grupo;
		}

		$this->setContenido('contenido');

		$this->checkValidationErrors();

		$id_emisor = $this->session_user;
		$contenido = $this->contenido;

		$statement =
			"INSERT INTO mensajes (contenido, id_emisor, $columna)
			VALUES (?, ?, ?)";

		$this->sqlAll(
			$statement,
			'sii',
			[
				$contenido,
				$id_emisor,
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

		$id_mensaje = $this->id_mensaje;
		$id_emisor = $this->session_user;

		$statement =
			"DELETE FROM mensajes
			WHERE id_mensaje = ?
			AND id_emisor = ?";

		$this->sqlAll(
			$statement,
			'ii',
			[
				$id_mensaje,
				$id_emisor
			]
		);

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	private function getNuevosMensajesDirectos(): array
	{
		$id_receptor = $this->id_receptor;
		$id_emisor = $this->session_user;

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
				$id_emisor,
				$id_receptor,
				$id_emisor,
				$id_receptor,
				$id_receptor,
				$id_emisor
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: GET NUEVOS MENSAJES GRUPALES

	private function getNuevosMensajesGrupales(): array
	{
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

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
				$id_usuario,
				$id_grupo,
				$id_grupo
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: STREAM MENSAJES

	public function streamMensajes(): void
	{

		$this->checkAllowedvalues([
			'id_receptor',
			'id_grupo'
		], 1);

		if (isset($_GET['id_receptor'])) {
			$this->setId('id_receptor');
			$mensajes = fn() => $this->getNuevosMensajesDirectos();
		} else if (isset($_GET['id_grupo'])) {
			$this->setId('id_grupo');
			$mensajes = fn() => $this->getNuevosMensajesGrupales();
		}

		$this->checkValidationErrors();

		$this->setSSE();


		$lastPing = 0;

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

			if (time() - $lastPing > 10) {
				$this->keepAlive();
				$lastPing = time();
			}

			usleep(300000); // 0.3s
		}
	}
}
