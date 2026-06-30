<?php

declare(strict_types=1);

require_once __DIR__ . '/Contacto.php';

readonly class Mensaje extends Contacto
{
	protected int $id_mensaje;
	protected int $id_receptor;
	protected string $contenido;

	public function __construct()
	{
		parent::__construct();
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
		$this->setId('id_grupo');
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

	// MARK: READ MENSAJES DIRECTOS

	public function readMensajesDirectos(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$user_min = min($this->session_user, $this->id_receptor);
		$user_max = max($this->session_user, $this->id_receptor);

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
			WHERE mensajes.id_receptor IS NOT NULL
			AND (
				LEAST(id_emisor, id_receptor) = ?
				AND GREATEST(id_emisor, id_receptor) = ?
			)
			ORDER BY fecha_envio ASC";

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

	public function readMensajesGrupales(): void
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

	// MARK: CREATE MENSAJE DIRECTO

	public function createMensajeDirecto(): void
	{
		$this->setId('id_receptor');
		$this->setContenido('contenido');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO mensajes (contenido, id_emisor, id_receptor)
			VALUES (?, ?, ?)";

		$this->executeQuery(
			$query,
			'sii',
			[
				$this->contenido,
				$this->session_user,
				$this->id_receptor
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: CREATE MENSAJE GRUPAL

	public function createMensajeGrupal(): void
	{
		$this->setId('id_grupo');
		$this->setContenido('contenido');
		$this->checkValidationErrors();

		$this->isMiembroGrupo();

		$query =
			"INSERT INTO mensajes (contenido, id_emisor, id_grupo)
			VALUES (?, ?, ?)";

		$this->executeQuery(
			$query,
			'sii',
			[
				$this->contenido,
				$this->session_user,
				$this->id_grupo
			]
		);

		$this->status = 201;
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

	protected function streamMensajesGeneric(callable $getter): void
	{
		$mensajes = $getter();

		if (!empty($mensajes)) {

			foreach ($mensajes as $m) {
				$ultimo_id = $m["id_mensaje"];
				$this->sendEvent('mensaje', $m);
			}

			$this->sendEvent('new mensaje', $ultimo_id);
		}
	}

	public function streamMensajesDirectos(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

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
		$this->setId('id_grupo');
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
