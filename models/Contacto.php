<?php

declare(strict_types=1);

require_once __DIR__ . '/Invitacion.php';

readonly class Contacto extends Invitacion
{
	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: OBTAIN CONTACTOS

	private function obtainContactos(): array
	{
		$dateFormat = self::ISO8601_SQL_FORMAT;

		$query =
			"SELECT *
			FROM (
				-- CHATS DIRECTOS (usuarios)
				SELECT
					u.id_usuario AS id,
					u.nombre_usuario AS nombre,
					'usuario' AS tipo,
					COUNT(m.id_mensaje) AS num_mensajes,
					DATE_FORMAT(ult.fecha_envio, $dateFormat) AS fecha_envio,
					ult.contenido,
					ult.id_emisor,
					ue.nombre_usuario AS nombre_emisor
				FROM usuarios u

				JOIN contactos_directos cd
					ON cd.id_usuario = ?
					AND cd.id_contacto = u.id_usuario

				LEFT JOIN ultimos_mensajes_leidos_directos uml
					ON uml.id_usuario = ?
					AND uml.id_receptor = u.id_usuario

				LEFT JOIN mensajes m
					ON m.id_receptor = ?
					AND m.id_emisor = u.id_usuario
					AND m.id_grupo IS NULL
					AND m.id_mensaje > COALESCE(uml.id_mensaje, 0)

				LEFT JOIN mensajes ult
					ON ult.id_mensaje = (
						SELECT MAX(m2.id_mensaje)
						FROM mensajes m2
						WHERE m2.id_grupo IS NULL
						AND (
								(m2.id_emisor = ? AND m2.id_receptor = u.id_usuario)
							OR  (m2.id_emisor = u.id_usuario AND m2.id_receptor = ?)
						)
					)

				LEFT JOIN usuarios ue
					ON ue.id_usuario = ult.id_emisor

				WHERE u.id_usuario != ?

				GROUP BY
					u.id_usuario,
					u.nombre_usuario,
					ult.fecha_envio,
					ult.contenido,
					ult.id_emisor,
					ue.nombre_usuario

				UNION ALL

				-- CHATS GRUPALES (grupos)
				SELECT
					g.id_grupo AS id,
					g.nombre_grupo AS nombre,
					'grupo' AS tipo,
					COUNT(mg.id_mensaje) AS num_mensajes,
					DATE_FORMAT(ultg.fecha_envio, $dateFormat) AS fecha_envio,
					ultg.contenido,
					ultg.id_emisor,
					ue2.nombre_usuario AS nombre_emisor
				FROM grupos g

				JOIN contactos_grupales mem
					ON mem.id_grupo = g.id_grupo
					AND mem.id_usuario = ?

				LEFT JOIN ultimos_mensajes_leidos_grupales umlg
					ON umlg.id_usuario = ?
					AND umlg.id_grupo = g.id_grupo

				LEFT JOIN mensajes mg
					ON mg.id_grupo = g.id_grupo
					AND mg.id_mensaje > COALESCE(umlg.id_mensaje, 0)

				LEFT JOIN mensajes ultg
					ON ultg.id_mensaje = (
						SELECT MAX(m3.id_mensaje)
						FROM mensajes m3
						WHERE m3.id_grupo = g.id_grupo
					)

				LEFT JOIN usuarios ue2
					ON ue2.id_usuario = ultg.id_emisor

				GROUP BY
					g.id_grupo,
					g.nombre_grupo,
					ultg.fecha_envio,
					ultg.contenido,
					ultg.id_emisor,
					ue2.nombre_usuario
			) AS chats
			ORDER BY
				(fecha_envio IS NULL) ASC,
				fecha_envio DESC,
				nombre ASC";

		$contactos = $this->executeQuery(
			$query,
			'iiiiiiii',
			[
				$this->session_user,
				$this->session_user,
				$this->session_user,
				$this->session_user,
				$this->session_user,
				$this->session_user,
				$this->session_user,
				$this->session_user
			],
			SqlReturn::FetchAll
		);

		return $contactos;
	}

	protected function streamContactosLogic(): void
	{
		static $contactos = [];

		$contactosUpdate = $this->obtainContactos();

		if ($contactosUpdate !== $contactos) {
			$this->sendEvent('new update', $contactosUpdate);
			$contactos = $contactosUpdate;
		}
	}

	// MARK: STREAM CONTACTOS

	public function streamContactos(): void
	{
		$this->setSSE([$this, 'streamContactosLogic']);
	}
}
