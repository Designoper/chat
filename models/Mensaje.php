<?php

declare(strict_types=1);

require_once __DIR__ . '/Contacto.php';

readonly class Mensaje extends Contacto
{
	protected string $ulid_mensaje;
	protected string $contenido;
	protected array $imagen;

	private const string FOLDER = '/mensajes/';

	private const string SQL_COLUMN = 'imagen';
	private const string SQL_TABLE = 'mensajes';
	private const string SQL_PRIMARY_KEY = 'ulid_mensaje';

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: CAN VIEW MENSAJE DIRECTO

	protected function canViewMensajeDirecto(): void
	{
		$query =
			"SELECT 1
			FROM mensajes
			WHERE ulid_mensaje = ?
			AND (
				(ulid_emisor = ? AND ulid_contacto = ?)
				OR
				(ulid_emisor = ? AND ulid_contacto = ?)
			)";

		$esDeLaConversacion = $this->executeQuery(
			$query,
			'sssss',
			[
				$this->ulid_mensaje,
				$this->session_ulid,
				$this->ulid_contacto,
				$this->ulid_contacto,
				$this->session_ulid
			],
			SqlReturn::FetchColumn
		);

		if (!$esDeLaConversacion) {
			$this->status = 403;
			$this->errors->setIntegrityError('Este mensaje no pertenece a esta conversación');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: CAN VIEW MENSAJE GRUPAL

	protected function canViewMensajeGrupal(): void
	{
		$query =
			"SELECT 1
			FROM mensajes
			WHERE ulid_mensaje = ?
			AND (
				(ulid_emisor = ? AND ulid_contacto = ?)
				OR
				(ulid_emisor = ? AND ulid_contacto = ?)
			)";

		$esDeLaConversacion = $this->executeQuery(
			$query,
			'sssss',
			[
				$this->ulid_mensaje,
				$this->session_ulid,
				$this->ulid_contacto,
				$this->ulid_contacto,
				$this->session_ulid
			],
			SqlReturn::FetchColumn
		);

		if (!$esDeLaConversacion) {
			$this->status = 403;
			$this->errors->setIntegrityError('Este mensaje no pertenece a esta conversación');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: GET ULTIMO ID

	public function getUltimoIdMensaje(): void
	{
		if (isset($_GET['ulid_contacto'])) {
			$this->getUltimoIdDirecto();
		}

		if (isset($_GET['ulid_grupo'])) {
			$this->getUltimoIdGrupal();
		}
	}

	// MARK: GET ULTIMO ID DIRECTO

	private function getUltimoIdDirecto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		// $this->isContacto();

		$query =
			"SELECT COALESCE((
				SELECT ulid_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE ulid_usuario = ?
				AND ulid_contacto = ?
			), '') AS ulid_mensaje";

		$last_id = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_ulid,
				$this->ulid_contacto
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
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"SELECT COALESCE((
				SELECT ulid_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			), '') AS ulid_mensaje";

		$last_id = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_ulid,
				$this->ulid_grupo
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
		if (isset($_POST['ulid_contacto'])) {
			$this->setUltimoIdDirecto();
		}

		if (isset($_POST['ulid_grupo'])) {
			$this->setUltimoIdGrupal();
		}
	}

	// MARK: SET ULTIMO ID DIRECTO

	private function setUltimoIdDirecto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setUlid('ulid_mensaje');
		$this->checkValidationErrors();

		// $this->isContacto();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_directos (ulid_usuario, ulid_contacto, ulid_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE ulid_mensaje = ?";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_ulid,
				$this->ulid_contacto,
				$this->ulid_mensaje,
				$this->ulid_mensaje
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID GRUPAL

	private function setUltimoIdGrupal(): void
	{
		$this->setUlid('ulid_grupo');
		$this->setUlid('ulid_mensaje');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_grupales (ulid_usuario, ulid_grupo, ulid_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE ulid_mensaje = ?";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_ulid,
				$this->ulid_grupo,
				$this->ulid_mensaje,
				$this->ulid_mensaje
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		// $this->isContacto();

		$user_min = min($this->session_ulid, $this->ulid_contacto);
		$user_max = max($this->session_ulid, $this->ulid_contacto);

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT
				mensajes.ulid_mensaje,
				mensajes.contenido,
				mensajes.imagen,
				DATE_FORMAT(mensajes.fecha_creacion, $dateFormat) AS fecha_creacion,
				mensajes.ulid_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.ulid_emisor = usuarios.ulid_usuario
			WHERE mensajes.ulid_contacto IS NOT NULL
			AND (
				LEAST(ulid_emisor, ulid_contacto) = ?
				AND GREATEST(ulid_emisor, ulid_contacto) = ?
			)
			ORDER BY fecha_creacion ASC";

		$mensajes = $this->executeQuery(
			$query,
			"ss",
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

	// MARK: READ IMAGEN MENSAJE DIRECTO

	public function readImagenMensajeDirecto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setUlid('ulid_mensaje');
		$this->checkValidationErrors();

		$this->isContacto();
		$this->canViewMensajeDirecto();

		$this->showFile();
	}

	// MARK: READ MENSAJES GRUPALES

	public function readMensajesGrupales(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT
				mensajes.ulid_mensaje,
				mensajes.contenido,
				mensajes.imagen,
				DATE_FORMAT(mensajes.fecha_creacion, $dateFormat) AS fecha_creacion,
				mensajes.ulid_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.ulid_emisor = usuarios.ulid_usuario
			WHERE mensajes.ulid_grupo = ?
			ORDER BY fecha_creacion ASC";

		$mensajes = $this->executeQuery(
			$query,
			"s",
			[
				$this->ulid_grupo
			],
			SqlReturn::FetchAll
		);

		$this->status = 200;
		$this->content = $mensajes;
		$this->sendResponse();
	}

	// MARK: READ IMAGEN MENSAJE GRUPAL

	public function readImagenMensajeGrupal(): void
	{
		$this->setUlid('ulid_grupo');
		$this->setUlid('ulid_mensaje');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$this->showFile();
	}

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setContenido('contenido');
		$this->checkValidationErrors();

		// $this->isContacto();

		$ulid = $this->generateUlid();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, contenido, ulid_emisor, ulid_contacto)
			VALUES (?, ?, ?, ?)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$ulid,
				$this->contenido,
				$this->session_ulid,
				$this->ulid_contacto
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE DIRECTO IMAGEN

	public function createMensajeDirectoImagen(): void
	{
		$this->setUlid('ulid_contacto');
		$this->setImagen('imagen');
		$this->checkValidationErrors();

		// $this->isContacto();

		$ulid_mensaje = $this->generateUlid();

		$this->file = $this->imagen;
		$this->extraDirectories = self::FOLDER;
		$file_name = $this->uploadFileName();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, imagen, ulid_emisor, ulid_contacto)
			VALUES (?, ?, ?, ?)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$ulid_mensaje,
				$file_name,
				$this->session_ulid,
				$this->ulid_contacto
			]
		);

		$this->uploadFile();
		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->setUlid('ulid_grupo');
		$this->setContenido('contenido');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$ulid = $this->generateUlid();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, contenido, ulid_emisor, ulid_grupo)
			VALUES (?, ?, ?, ?)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$ulid,
				$this->contenido,
				$this->session_ulid,
				$this->ulid_grupo
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL IMAGEN

	public function createMensajeGrupalImagen(): void
	{
		$this->setUlid('ulid_grupo');
		$this->setImagen('imagen');
		$this->checkValidationErrors();

		// $this->isContacto();

		$ulid_mensaje = $this->generateUlid();

		$this->file = $this->imagen;
		$this->extraDirectories = self::FOLDER;
		$file_name = $this->uploadFileName();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, imagen, ulid_emisor, ulid_grupo)
			VALUES (?, ?, ?, ?)";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$ulid_mensaje,
				$file_name,
				$this->session_ulid,
				$this->ulid_grupo
			]
		);

		$this->uploadFile();
		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: IS AUTOR MENSAJE

	private function isAutorMensaje(): void
	{
		$query =
			"SELECT ulid_emisor
			FROM mensajes
			WHERE ulid_mensaje = ?";

		$autor = $this->executeQuery(
			$query,
			"s",
			[
				$this->ulid_mensaje
			],
			SqlReturn::FetchColumn
		);

		if ($autor !== $this->session_ulid) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres el autor del mensaje');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: DELETE MENSAJE

	public function deleteMensaje(): void
	{
		$this->setUlid('ulid_mensaje');
		$this->checkValidationErrors();
		$this->isAutorMensaje();

		$fileUrl = $this->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $this->ulid_mensaje);

		$query =
			"DELETE FROM mensajes
			WHERE ulid_mensaje = ?";

		$this->executeQuery(
			$query,
			's',
			[
				$this->ulid_mensaje
			]
		);

		$this->status = 204;
		$this->deleteFile($fileUrl);
		$this->sendResponse();
	}

	// MARK: GET NUEVOS MENSAJES DIRECTOS

	private function getNuevosMensajesDirectos(): array
	{
		$user_min = min($this->session_ulid, $this->ulid_contacto);
		$user_max = max($this->session_ulid, $this->ulid_contacto);

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT mensajes.ulid_mensaje,
				mensajes.contenido,
				mensajes.imagen,
				DATE_FORMAT(mensajes.fecha_creacion, $dateFormat) AS fecha_creacion,
				mensajes.ulid_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.ulid_emisor = usuarios.ulid_usuario
			WHERE mensajes.ulid_mensaje > COALESCE((
				SELECT ulid_mensaje
				FROM ultimos_mensajes_leidos_directos
				WHERE ulid_usuario = ?
				AND ulid_contacto = ?
			), '')
			AND (
				LEAST(ulid_emisor, ulid_contacto) = ?
				AND GREATEST(ulid_emisor, ulid_contacto) = ?
			)
			AND mensajes.ulid_grupo IS NULL
			ORDER BY mensajes.ulid_mensaje ASC";

		$mensajes = $this->executeQuery(
			$query,
			"ssss",
			[
				$this->session_ulid,
				$this->ulid_contacto,
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
			"SELECT mensajes.ulid_mensaje,
				mensajes.contenido,
				mensajes.imagen,
				DATE_FORMAT(mensajes.fecha_creacion, $dateFormat) AS fecha_creacion,
				mensajes.ulid_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.ulid_emisor = usuarios.ulid_usuario
	        WHERE mensajes.ulid_mensaje > COALESCE((
				SELECT ulid_mensaje
				FROM ultimos_mensajes_leidos_grupales
				WHERE ulid_usuario = ?
				AND ulid_grupo = ?
			), '')
	        AND mensajes.ulid_contacto IS NULL
			AND mensajes.ulid_grupo = ?
	        ORDER BY mensajes.ulid_mensaje ASC";

		$mensajes = $this->executeQuery(
			$query,
			"sss",
			[
				$this->session_ulid,
				$this->ulid_grupo,
				$this->ulid_grupo
			],
			SqlReturn::FetchAll
		);

		return $mensajes;
	}

	// MARK: STREAM MENSAJES

	protected function streamMensajesGeneric(callable $getter): void
	{
		$mensajes = $getter();

		if (!empty($mensajes)) {

			foreach ($mensajes as $m) {
				$ultimo_id = $m["ulid_mensaje"];
				$this->sendEvent('mensaje', $m);
			}

			$this->sendEvent('new mensaje', $ultimo_id);
		}
	}

	public function streamMensajesDirectos(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$this->isContacto();

		$this->setSSE(
			fn() =>
			$this->streamMensajesGeneric(
				fn() =>
				$this->getNuevosMensajesDirectos()
			)
		);
	}

	public function streamMensajesGrupales(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$this->setSSE(
			fn() =>
			$this->streamMensajesGeneric(
				fn() =>
				$this->getNuevosMensajesGrupales()
			)
		);
	}
}
