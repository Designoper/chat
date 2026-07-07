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

	// ============================================================================
	// MARK: CAN VIEW ARCHIVO DIRECTO
	// ============================================================================
	protected function canViewArchivoDirecto(): void
	{
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM mensajes
				WHERE ulid_mensaje = ?
				AND (ulid_emisor, ulid_contacto) IN
				(
					(?, ?),
					(?, ?)
				)
			)";

		$params = [
			['s', $this->ulid_mensaje],
			['s', $this->session_ulid],
			['s', $this->ulid_contacto],
			['s', $this->ulid_contacto],
			['s', $this->session_ulid]
		];

		$mensaje_directo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$mensaje_directo) {
			$this->integrityErrorSetup(403, "Este archivo no pertenece a la conversación.");
		}
	}

	// ============================================================================
	// MARK: CAN VIEW ARCHIVO GRUPAL
	// ============================================================================
	protected function canViewArchivoGrupal(): void
	{
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM mensajes
				WHERE ulid_mensaje = ?
				AND ulid_grupo = ?
			)";

		$params = [
			['s', $this->ulid_mensaje],
			['s', $this->ulid_grupo]
		];

		$mensaje_grupo = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$mensaje_grupo) {
			$this->integrityErrorSetup(403, "No pertences al grupo de este archivo.");
		}
	}

	// ============================================================================
	// MARK: GET ULTIMO ULID TEMPLATE
	// ============================================================================
	private function getUltimoUlidTemplate(string $tipo_contacto): void
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

		$this->setProperties([fn() => $this->setUlid($config['ulid'])]);

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

		$params = [
			['s', $this->session_ulid],
			['s', $this->{$config['ulid']}]
		];

		$last_ulid = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		$this->sendOkResponse(200, $last_ulid);
	}

	// ============================================================================
	// MARK: GET ULTIMO ULID DIRECTO
	// ============================================================================
	public function getUltimoUlidDirecto(): void
	{
		$this->getUltimoUlidTemplate('contacto');
	}

	// ============================================================================
	// MARK: GET ULTIMO ULID GRUPAL
	// ============================================================================
	public function getUltimoUlidGrupal(): void
	{
		$this->getUltimoUlidTemplate('grupo');
	}

	// ============================================================================
	// MARK: SET ULTIMO ULID TEMPLATE
	// ============================================================================
	private function setUltimoUlidTemplate(string $tipo_contacto): void
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

		$this->setProperties([
			fn() => $this->setUlid($config['ulid']),
			fn() => $this->setUlid('ulid_mensaje')
		]);

		$config['security']();

		$query =
			"INSERT INTO ultimos_mensajes_leidos_{$config['tipo']} (ulid_usuario, {$config['ulid']}, ulid_mensaje)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY
			UPDATE ulid_mensaje = ?";

		$params = [
			['s', $this->session_ulid],
			['s', $this->{$config['ulid']}],
			['s', $this->ulid_mensaje],
			['s', $this->ulid_mensaje]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(201);
	}

	// ============================================================================
	// MARK: SET ULTIMO ULID DIRECTO
	// ============================================================================
	public function setUltimoUlidDirecto(): void
	{
		$this->setUltimoUlidTemplate('contacto');
	}

	// ============================================================================
	// MARK: SET ULTIMO ULID GRUPAL
	// ============================================================================
	public function setUltimoUlidGrupal(): void
	{
		$this->setUltimoUlidTemplate('grupo');
	}

	// ============================================================================
	// MARK: READ MENSAJAES DIRECTOS
	// ============================================================================
	public function readMensajesDirectos(): void
	{

		$this->setProperties([fn() => $this->setUlid('ulid_contacto')]);

		$this->isContacto();

		$ulid_min = min($this->session_ulid, $this->ulid_contacto);
		$ulid_max = max($this->session_ulid, $this->ulid_contacto);

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

		$params = [
			['s', $ulid_min],
			['s', $ulid_max]
		];

		$mensajes = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		$this->sendOkResponse(200, $mensajes);
	}

	// ============================================================================
	// MARK: READ ARCHIVO MENSAJE DIRECTO
	// ============================================================================
	public function readArchivoMensajeDirecto(): void
	{
		$this->setProperties([
			fn() => $this->setUlid('ulid_contacto'),
			fn() => $this->setUlid('ulid_mensaje')
		]);

		$this->isContacto();
		$this->canViewArchivoDirecto();

		$this->showFile();
	}

	// ============================================================================
	// MARK: READ MENSAJES GRUPALES
	// ============================================================================
	public function readMensajesGrupales(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

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

		$params = [['s', $this->ulid_grupo]];

		$mensajes = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		$this->sendOkResponse(200, $mensajes);
	}

	// ============================================================================
	// MARK: READ ARCHIVO MENSAJE GRUPAL
	// ============================================================================
	public function readArchivoMensajeGrupal(): void
	{
		$this->setProperties([
			fn() => $this->setUlid('ulid_grupo'),
			fn() => $this->setUlid('ulid_mensaje')
		]);

		$this->isMiembroGrupo();
		$this->canViewArchivoGrupal();

		$this->showFile();
	}

	// ============================================================================
	// MARK: CREATE MENSAJE TEMPLATE
	// ============================================================================
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
				'upload' => fn() => $this->uploadFile($filetype),
				'extra' => function () use ($filetype) {
					$this->file = $this->archivo;
					$this->extraDirectories = self::FOLDER;
					return $this->uploadFileName($filetype);
				},
			]
		};

		// $this->setUlid($contacto['ulid']);
		// $archivo['set']();
		// $this->checkValidationErrors();

		$this->setProperties([
			fn() => $this->setUlid($contacto['ulid']),
			fn() => $archivo['set']()
		]);

		$contacto['security']();

		$this->ulid_mensaje = $this->generateUlid();
		$contenido = $archivo['extra']();

		$query =
			"INSERT INTO mensajes (ulid_mensaje, tipo_mensaje, contenido, ulid_emisor, {$contacto['ulid']})
			VALUES (?, ?, ?, ?, ?)";

		$params = [
			['s', $this->ulid_mensaje],
			['s', $filetype->value],
			['s', $contenido],
			['s', $this->session_ulid],
			['s', $this->{$contacto['ulid']}]
		];

		$this->executeQuery($query, $params);

		$archivo['upload']();

		$this->sendOkResponse(201);
	}

	// ============================================================================
	// MARK: CREATE MENSAJE DIRECTO
	// ============================================================================
	public function createMensajeDirecto(): void
	{
		$this->createMensajeTemplate(FileTypes::Text, 'contacto');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE DIRECTO IMAGEN
	// ============================================================================
	public function createMensajeDirectoImagen(): void
	{
		$this->createMensajeTemplate(FileTypes::Image, 'contacto');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE DIRECTO AUDIO
	// ============================================================================
	public function createMensajeDirectoAudio(): void
	{
		$this->createMensajeTemplate(FileTypes::Audio, 'contacto');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE DIRECTO VIDEO
	// ============================================================================
	public function createMensajeDirectoVideo(): void
	{
		$this->createMensajeTemplate(FileTypes::Video, 'contacto');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE GRUPAL
	// ============================================================================
	public function createMensajeGrupal(): void
	{
		$this->createMensajeTemplate(FileTypes::Text, 'grupo');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE GRUPAL IMAGEN
	// ============================================================================
	public function createMensajeGrupalImagen(): void
	{
		$this->createMensajeTemplate(FileTypes::Image, 'grupo');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE GRUPAL AUDIO
	// ============================================================================
	public function createMensajeGrupalAudio(): void
	{
		$this->createMensajeTemplate(FileTypes::Audio, 'grupo');
	}

	// ============================================================================
	// MARK: CREATE MENSAJE GRUPAL VIDEO
	// ============================================================================
	public function createMensajeGrupalVideo(): void
	{
		$this->createMensajeTemplate(FileTypes::Video, 'grupo');
	}

	// ============================================================================
	// MARK: IS AUTOR MENSAJE
	// ============================================================================
	private function isAutorMensaje(): void
	{
		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM mensajes
				WHERE ulid_mensaje = ?
				AND ulid_emisor = ?
			)";

		$params = [
			['s', $this->ulid_mensaje],
			['s', $this->session_ulid]
		];

		$autor_mensaje = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$autor_mensaje) {
			$this->integrityErrorSetup(403, "No eres el autor del mensaje.");
		}
	}

	// ============================================================================
	// MARK: DELETE MENSAJE (FIX MENSAJE)
	// ============================================================================
	public function deleteMensaje(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_mensaje')]);

		$this->isAutorMensaje();

		$fileUrl = $this->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $this->ulid_mensaje);

		$query =
			"DELETE FROM mensajes
			WHERE ulid_mensaje = ?";

		$params = [['s', $this->ulid_mensaje]];

		$this->executeQuery($query, $params);

		$this->deleteFile($fileUrl);
		$this->sendOkResponse(204);
	}

	// ============================================================================
	// MARK: GET NUEVOS MENSAJES DIRECTOS
	// ============================================================================
	private function getNuevosMensajesDirectos(): array
	{
		$ulid_min = min($this->session_ulid, $this->ulid_contacto);
		$ulid_max = max($this->session_ulid, $this->ulid_contacto);

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

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_contacto],
			['s', $ulid_min],
			['s', $ulid_max]
		];

		$mensajes = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		return $mensajes;
	}

	// ============================================================================
	// MARK: GET NUEVOS MENSAJES GRUPALES
	// ============================================================================
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

		$params = [
			['s', $this->session_ulid],
			['s', $this->ulid_grupo],
			['s', $this->ulid_grupo]
		];

		$mensajes = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		return $mensajes;
	}

	// ============================================================================
	// MARK: STREAM MENSAJES GENERIC
	// ============================================================================
	private function streamMensajesGeneric(callable $getter): void
	{
		$mensajes = $getter();

		if (!empty($mensajes)) {
			foreach ($mensajes as $mensaje) {
				$ultimo_ulid = $mensaje["ulid_mensaje"];
				$this->sendEvent('mensaje', $mensaje, $ultimo_ulid);
			}
		}
	}

	// ============================================================================
	// MARK: STREAM MENSAJES DIRECTOS
	// ============================================================================
	public function streamMensajesDirectos(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_contacto')]);

		$this->isContacto();

		$this->setSSE(fn() => $this->streamMensajesGeneric(fn() => $this->getNuevosMensajesDirectos()));
	}

	// ============================================================================
	// MARK: STREAM MENSAJES GRUPALES
	// ============================================================================
	public function streamMensajesGrupales(): void
	{
		$this->setProperties([fn() => $this->setUlid('ulid_grupo')]);

		$this->isMiembroGrupo();

		$this->setSSE(fn() => $this->streamMensajesGeneric(fn() => $this->getNuevosMensajesGrupales()));
	}
}
