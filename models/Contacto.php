<?php

declare(strict_types=1);

require_once __DIR__ . "/Invitacion.php";

readonly class Contacto extends Invitacion
{
	public function __construct()
	{
		parent::__construct();
	}

	// ============================================================================
	// MARK: OBTAIN CONTACTOS
	// ============================================================================
	private function obtainContactos(): array
	{
		$query =
			"SELECT *
			FROM (
				-- CHATS DIRECTOS (usuarios)
				SELECT
					u.ulid_usuario AS ulid,
					u.nombre_usuario AS nombre,
					'usuario' AS tipo,
					COUNT(m.ulid_mensaje) AS num_mensajes,
					DATE_FORMAT(ult.fecha_creacion, :date_format) AS fecha_creacion,
					ult.contenido,
					ult.ulid_emisor,
					ult.tipo_mensaje,
					ue.nombre_usuario AS nombre_emisor
				FROM usuarios u

				JOIN contactos_directos cd
					ON cd.ulid_min = LEAST(:session_ulid, u.ulid_usuario)
					AND cd.ulid_max = GREATEST(:session_ulid, u.ulid_usuario)

				LEFT JOIN ultimos_mensajes_leidos_directos uml
					ON uml.ulid_usuario = :session_ulid
					AND uml.ulid_contacto = u.ulid_usuario

				LEFT JOIN mensajes m
					ON m.ulid_contacto = :session_ulid
					AND m.ulid_emisor = u.ulid_usuario
					AND m.ulid_mensaje > COALESCE(uml.ulid_mensaje, '')

				LEFT JOIN mensajes ult
					ON ult.ulid_mensaje = (
						SELECT MAX(m2.ulid_mensaje)
						FROM mensajes m2
						WHERE m2.ulid_grupo IS NULL
						AND (
								(m2.ulid_emisor = :session_ulid AND m2.ulid_contacto = u.ulid_usuario)
							OR  (m2.ulid_emisor = u.ulid_usuario AND m2.ulid_contacto = :session_ulid)
						)
					)

				LEFT JOIN usuarios ue
					ON ue.ulid_usuario = ult.ulid_emisor

				WHERE u.ulid_usuario != :session_ulid

				GROUP BY
					u.ulid_usuario,
					u.nombre_usuario,
					ult.fecha_creacion,
					ult.contenido,
					ult.ulid_emisor,
					ult.tipo_mensaje,
					ue.nombre_usuario

				UNION ALL

				-- CHATS GRUPALES (grupos)
				SELECT
					g.ulid_grupo AS ulid,
					g.nombre_grupo AS nombre,
					'grupo' AS tipo,
					COUNT(mg.ulid_mensaje) AS num_mensajes,
					DATE_FORMAT(ultg.fecha_creacion, :date_format) AS fecha_creacion,
					ultg.contenido,
					ultg.ulid_emisor,
					ultg.tipo_mensaje,
					ue2.nombre_usuario AS nombre_emisor
				FROM grupos g

				JOIN contactos_grupales mem
					ON mem.ulid_grupo = g.ulid_grupo
					AND mem.ulid_usuario = :session_ulid

				LEFT JOIN ultimos_mensajes_leidos_grupales umlg
					ON umlg.ulid_usuario = :session_ulid
					AND umlg.ulid_grupo = g.ulid_grupo

				LEFT JOIN mensajes mg
					ON mg.ulid_grupo = g.ulid_grupo
					AND mg.ulid_mensaje > COALESCE(umlg.ulid_mensaje, '')

				LEFT JOIN mensajes ultg
					ON ultg.ulid_mensaje = (
						SELECT MAX(m3.ulid_mensaje)
						FROM mensajes m3
						WHERE m3.ulid_grupo = g.ulid_grupo
					)

				LEFT JOIN usuarios ue2
					ON ue2.ulid_usuario = ultg.ulid_emisor

				GROUP BY
					g.ulid_grupo,
					g.nombre_grupo,
					ultg.fecha_creacion,
					ultg.contenido,
					ultg.ulid_emisor,
					ultg.tipo_mensaje,
					ue2.nombre_usuario
			) AS chats
			ORDER BY
				(fecha_creacion IS NULL) ASC,
				fecha_creacion DESC,
				nombre ASC";

		// Mapeo limpio en PDO: no importa cuántas veces aparezca ":session_ulid" o ":date_format"
		// en el texto superior, aquí solo necesitas declararlos una vez.
		$params = [
			"session_ulid" => $this->session_ulid,
			"date_format"  => self::ISO8601_SQL_FORMAT
		];

		// 1. Obtenemos la instancia de conexión PDO desde tu clase base (ajusta el nombre si es necesario)
		// 2. Activamos la emulación temporalmente para que PHP gestione la repetición de placeholders
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

		// 3. Ejecutamos la consulta normalmente
		$contactos = $this->executeQuery($query, $params, SqlReturn::FetchAll);

		// 4. MUY IMPORTANTE: Desactivamos la emulación de nuevo para mantener el resto de la API segura
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		return $contactos;
	}

	// ============================================================================
	// MARK: STREAM CONTACTOS LOGIC
	// ============================================================================
	protected function streamContactosLogic(): void
	{
		static $contactos = [];

		$contactosUpdate = $this->obtainContactos();

		if ($contactosUpdate !== $contactos) {
			$this->sendEvent("new update", $contactosUpdate);
			$contactos = $contactosUpdate;
		}
	}

	// ============================================================================
	// MARK: STREAM CONTACTOS
	// ============================================================================
	public function streamContactos(): void
	{
		$this->setSSE([$this, "streamContactosLogic"]);
	}

	// ============================================================================
	// MARK: IS CONTACTO
	// ============================================================================
	protected function isContacto(): void
	{
		$ulid_min = min($this->session_ulid, $this->ulid_contacto);
		$ulid_max = max($this->session_ulid, $this->ulid_contacto);

		$query =
			"SELECT EXISTS(
				SELECT 1
				FROM contactos_directos
				WHERE ulid_min = :ulid_min
				AND ulid_max = :ulid_max
			)";

		$params = [
			"ulid_min" => $ulid_min,
			"ulid_max" => $ulid_max
		];

		$contacto = $this->executeQuery($query, $params, SqlReturn::Exists);

		if (!$contacto) {
			$this->integrityErrorSetup(403, "No eres contacto de este usuario.");
		}
	}
}
