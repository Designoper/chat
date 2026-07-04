<?php

declare(strict_types=1);

require_once __DIR__ . '/Contacto.php';

readonly class Mensaje extends Contacto
{
	protected string $ulid_mensaje;
	protected string $contenido;
	protected array $archivo;

	private const string FOLDER = 'mensajes/';

	private const string SQL_COLUMN = 'contenido';
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
			AND (ulid_emisor, ulid_contacto) IN
				(
					(?, ?),
					(?, ?)
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
			$this->errors->setIntegrityError('Este archivo no pertenece a esta conversación');
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
			AND ulid_grupo = ?";

		$esDeLaConversacion = $this->executeQuery(
			$query,
			'ss',
			[
				$this->ulid_mensaje,
				$this->ulid_grupo
			],
			SqlReturn::FetchColumn
		);

		if (!$esDeLaConversacion) {
			$this->status = 403;
			$this->errors->setIntegrityError('No pertences al grupo de este archivo.');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: GET ULTIMO ID TEMPLATE

	private function getUltimoIdTemplate(string $tipo_contacto): void
	{
		$config = match ($tipo_contacto) {
			'contacto' => [
				'ulid' => 'ulid_contacto',
				'tipo' => 'directos',
				'security' => fn() => $this->isContacto(),
			],
			'grupo' => [
				'ulid' => 'ulid_grupo',
				'tipo' => 'grupales',
				'security' => fn() => $this->isMiembroGrupo(),
			]
		};

		$this->setUlid($config['ulid']);
		$this->checkValidationErrors();

		$config['security']();

		$query =
			"SELECT COALESCE(
				(
					SELECT ulid_mensaje
					FROM ultimos_mensajes_leidos_{$config['tipo']}
					WHERE ulid_usuario = ?
					AND {$config['ulid']} = ?
				),
			'') AS ulid_mensaje";

		$last_id = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_ulid,
				$this->{$config['ulid']}
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $last_id;
		$this->sendResponse();
	}

	// MARK: GET ULTIMO ID DIRECTO

	public function getUltimoIdDirecto(): void
	{
		$this->getUltimoIdTemplate('contacto');
	}

	// MARK: GET ULTIMO ID GRUPAL

	public function getUltimoIdGrupal(): void
	{
		$this->getUltimoIdTemplate('grupo');
	}

	// MARK: SET ULTIMO ID TEMPLATE

	private function setUltimoIdTemplate(string $tipo_contacto): void
	{
		$config = match ($tipo_contacto) {
			'contacto' => [
				'ulid' => 'ulid_contacto',
				'tipo' => 'directos',
				'security' => fn() => $this->isContacto(),
			],
			'grupo' => [
				'ulid' => 'ulid_grupo',
				'tipo' => 'grupales',
				'security' => fn() => $this->isMiembroGrupo(),
			]
		};

		$this->setUlid($config['ulid']);
		$this->setUlid('ulid_mensaje');
		$this->checkValidationErrors();

		$config['security']();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_{$config['tipo']} (ulid_usuario, {$config['ulid']}, ulid_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE ulid_mensaje = ?";

		$this->executeQuery(
			$query,
			'ssss',
			[
				$this->session_ulid,
				$this->{$config['ulid']},
				$this->ulid_mensaje,
				$this->ulid_mensaje
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: SET ULTIMO ID DIRECTO

	public function setUltimoIdDirecto(): void
	{
		$this->setUltimoIdTemplate('contacto');
	}

	// MARK: SET ULTIMO ID GRUPAL

	public function setUltimoIdGrupal(): void
	{
		$this->setUltimoIdTemplate('grupo');
	}

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$this->isContacto();

		$user_min = min($this->session_ulid, $this->ulid_contacto);
		$user_max = max($this->session_ulid, $this->ulid_contacto);

		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT
				mensajes.ulid_mensaje,
				mensajes.tipo_mensaje,
				mensajes.contenido,
				DATE_FORMAT(mensajes.fecha_creacion, $dateFormat) AS fecha_creacion,
				mensajes.ulid_emisor,
				usuarios.nombre_usuario
			FROM mensajes
			LEFT JOIN usuarios
				ON mensajes.ulid_emisor = usuarios.ulid_usuario
			WHERE (
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

	// MARK: READ ARCHIVO MENSAJE DIRECTO

	public function readArchivoMensajeDirecto(): void
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
				mensajes.tipo_mensaje,
				mensajes.contenido,
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

	// MARK: READ ARCHIVO MENSAJE GRUPAL

	public function readArchivoMensajeGrupal(): void
	{
		$this->setUlid('ulid_grupo');
		$this->setUlid('ulid_mensaje');

		$this->checkValidationErrors();

		$this->isMiembroGrupo();
		$this->canViewMensajeGrupal();

		$this->showFile();
	}

	// MARK: CREATE MENSAJE TEMPLATE

	private function createMensajeTemplate(FileTypes $filetype, string $tipo_contacto): void
	{
		$contacto = match ($tipo_contacto) {
			'contacto' => [
				'ulid' => 'ulid_contacto',
				'security' => fn() => $this->isContacto(),
			],
			'grupo' => [
				'ulid' => 'ulid_grupo',
				'security' => fn() => $this->isMiembroGrupo(),
			]
		};

		$archivo = match ($filetype) {
			FileTypes::Text => [
				'set' => fn() => $this->setContenido('contenido'),
				'upload' => fn() => null,
				'extra' => fn() => $this->contenido,
			],
			default => [
				'set' => fn() => $this->setArchivo('archivo', $filetype),
				'upload' => fn() => $this->uploadFile(),
				'extra' => function () {
					$this->file = $this->archivo;
					$this->extraDirectories = self::FOLDER;
					return $this->uploadFileName();
				},
			]
		};

		$this->setUlid($contacto['ulid']);
		$archivo['set']();
		$this->checkValidationErrors();
		$contacto['security']();

		$this->ulid_mensaje = $this->generateUlid();
		$contenido = $archivo['extra']();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, tipo_mensaje, contenido, ulid_emisor, {$contacto['ulid']})
			VALUES (?, ?, ?, ?, ?)";

		$this->executeQuery(
			$query,
			'sssss',
			[
				$this->ulid_mensaje,
				$filetype->value,
				$contenido,
				$this->session_ulid,
				$this->{$contacto['ulid']}
			]
		);

		$archivo['upload']();

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE DIRECTO TEXTO

	public function createMensajeDirecto(): void
	{
		$this->createMensajeTemplate(FileTypes::Text, 'contacto');
	}

	// MARK: CREATE MENSAJE DIRECTO IMAGEN

	public function createMensajeDirectoImagen(): void
	{
		$this->createMensajeTemplate(FileTypes::Image, 'contacto');
	}

	// MARK: CREATE MENSAJE DIRECTO AUDIO

	public function createMensajeDirectoAudio(): void
	{
		$this->createMensajeTemplate(FileTypes::Audio, 'contacto');
	}

	// MARK: CREATE MENSAJE DIRECTO VIDEO

	public function createMensajeDirectoVideo(): void
	{
		$this->createMensajeTemplate(FileTypes::Video, 'contacto');
	}

	// MARK: CREATE MENSAJE GRUPAL TEXTTO

	public function createMensajeGrupal(): void
	{
		$this->createMensajeTemplate(FileTypes::Text, 'grupo');
	}

	// MARK: CREATE MENSAJE GRUPAL IMAGEN

	public function createMensajeGrupalImagen(): void
	{
		$this->createMensajeTemplate(FileTypes::Image, 'grupo');
	}

	// MARK: CREATE MENSAJE GRUPAL AUDIO

	public function createMensajeGrupalAudio(): void
	{
		$this->createMensajeTemplate(FileTypes::Audio, 'grupo');
	}

	// MARK: CREATE MENSAJE GRUPAL VIDEO

	public function createMensajeGrupalVideo(): void
	{
		$this->createMensajeTemplate(FileTypes::Video, 'grupo');
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

	// MARK: DELETE MENSAJE (FIX IMAGE)

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
				mensajes.tipo_mensaje,
				mensajes.contenido,
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
				mensajes.tipo_mensaje,
				mensajes.contenido,
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

	private function streamMensajesGeneric(callable $getter): void
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

		$this->setSSE(fn() => $this->streamMensajesGeneric(fn() => $this->getNuevosMensajesDirectos()));
	}

	public function streamMensajesGrupales(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$this->setSSE(fn() => $this->streamMensajesGeneric(fn() => $this->getNuevosMensajesGrupales()));
	}
}
