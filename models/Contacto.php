<?php

declare(strict_types=1);

require_once __DIR__ . '/Invitacion.php';

readonly class Contacto extends Invitacion
{
	public function __construct()
	{
		parent::__construct();
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
					u.ulid_usuario AS ulid,
					u.nombre_usuario AS nombre,
					'usuario' AS tipo,
					COUNT(m.ulid_mensaje) AS num_mensajes,
					DATE_FORMAT(ult.fecha_envio, $dateFormat) AS fecha_envio,
					ult.contenido,
					ult.ulid_emisor,
					ue.nombre_usuario AS nombre_emisor
				FROM usuarios u

				-- JOIN contactos_directos cd
				-- 	ON cd.ulid_usuario = ?
				-- 	AND cd.ulid_contacto = u.ulid_usuario

				JOIN contactos_directos cd
					ON cd.ulid_a = LEAST(?, u.ulid_usuario)
					AND cd.ulid_b = GREATEST(?, u.ulid_usuario)

				LEFT JOIN ultimos_mensajes_leidos_directos uml
					ON uml.ulid_usuario = ?
					AND uml.ulid_contacto = u.ulid_usuario

				LEFT JOIN mensajes m
					ON m.ulid_contacto = ?
					AND m.ulid_emisor = u.ulid_usuario
					AND m.ulid_grupo IS NULL
					AND m.ulid_mensaje > COALESCE(uml.ulid_mensaje, 0)

				LEFT JOIN mensajes ult
					ON ult.ulid_mensaje = (
						SELECT MAX(m2.ulid_mensaje)
						FROM mensajes m2
						WHERE m2.ulid_grupo IS NULL
						AND (
								(m2.ulid_emisor = ? AND m2.ulid_contacto = u.ulid_usuario)
							OR  (m2.ulid_emisor = u.ulid_usuario AND m2.ulid_contacto = ?)
						)
					)

				LEFT JOIN usuarios ue
					ON ue.ulid_usuario = ult.ulid_emisor

				WHERE u.ulid_usuario != ?

				GROUP BY
					u.ulid_usuario,
					u.nombre_usuario,
					ult.fecha_envio,
					ult.contenido,
					ult.ulid_emisor,
					ue.nombre_usuario

				UNION ALL

				-- CHATS GRUPALES (grupos)
				SELECT
					g.ulid_grupo AS ulid,
					g.nombre_grupo AS nombre,
					'grupo' AS tipo,
					COUNT(mg.ulid_mensaje) AS num_mensajes,
					DATE_FORMAT(ultg.fecha_envio, $dateFormat) AS fecha_envio,
					ultg.contenido,
					ultg.ulid_emisor,
					ue2.nombre_usuario AS nombre_emisor
				FROM grupos g

				JOIN contactos_grupales mem
					ON mem.ulid_grupo = g.ulid_grupo
					AND mem.ulid_usuario = ?

				LEFT JOIN ultimos_mensajes_leidos_grupales umlg
					ON umlg.ulid_usuario = ?
					AND umlg.ulid_grupo = g.ulid_grupo

				LEFT JOIN mensajes mg
					ON mg.ulid_grupo = g.ulid_grupo
					AND mg.ulid_mensaje > COALESCE(umlg.ulid_mensaje, 0)

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
					ultg.fecha_envio,
					ultg.contenido,
					ultg.ulid_emisor,
					ue2.nombre_usuario
			) AS chats
			ORDER BY
				(fecha_envio IS NULL) ASC,
				fecha_envio DESC,
				nombre ASC";

		$contactos = $this->executeQuery(
			$query,
			'sssssssss',
			[
				$this->session_user,
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

	// MARK: IS CONTACTO FIX

	protected function isContacto(): void
	{
		$query =
			"SELECT 1
			FROM contactos_directos
			WHERE ulid_usuario = ?
			AND ulid_contacto = ?";

		$rol = $this->executeQuery(
			$query,
			'ss',
			[
				$this->session_user,
				$this->ulid_contacto
			],
			SqlReturn::BindResult
		);

		if (!$rol) {
			$this->status = 403;
			$this->errors->setIntegrityError('No eres contacto de este usuario');
			$this->checkIntegrityErrors();
		}
	}
}
