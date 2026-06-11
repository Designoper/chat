<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Contacto extends MysqliConnect
{
	public function __construct()
	{
		parent::__construct();

		$this->authEndpoint();
	}

	// MARK: READ CONTACTOS

	public function readContactos(): void
	{
		$contactos = $this->obtainContactos();

		$this->status = 200;
		$this->content = $contactos;
		$this->sendResponse();
	}

	// MARK: OBTAIN CONTACTOS

	private function obtainContactos(): array
	{
		$id_usuario = $this->session_user;

		$statement =
			"SELECT *
			FROM (
				-- CHATS DIRECTOS (usuarios)
				SELECT
					u.id_usuario AS id,
					u.nombre_usuario AS nombre,
					'usuario' AS tipo,
					COUNT(m.id_mensaje) AS num_mensajes,
					DATE_FORMAT(ult.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
					ult.contenido,
					ult.id_emisor
				FROM usuarios u
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
				WHERE u.id_usuario != ?
				GROUP BY u.id_usuario, u.nombre_usuario, ult.fecha_envio, ult.contenido

				UNION ALL

				-- CHATS GRUPALES (grupos)
				SELECT
					g.id_grupo AS id,
					g.nombre_grupo AS nombre,
					'grupo' AS tipo,
					COUNT(mg.id_mensaje) AS num_mensajes,
					DATE_FORMAT(ultg.fecha_envio, '%Y-%m-%dT%H:%i:%sZ') AS fecha_envio,
					ultg.contenido,
					ultg.id_emisor
				FROM grupos g
				JOIN membresias mem
					ON mem.id_grupo = g.id_grupo
					AND mem.id_usuario = ?
					AND mem.rol IN ('fundador','miembro')
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
				GROUP BY g.id_grupo, g.nombre_grupo, ultg.fecha_envio, ultg.contenido
			) AS chats

			ORDER BY
				(fecha_envio IS NULL) ASC,
				fecha_envio DESC,
				nombre ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiiiiii",
			$id_usuario,
			$id_usuario,
			$id_usuario,
			$id_usuario,
			$id_usuario,
			$id_usuario,
			$id_usuario
		);

		$query->execute();

		$contactos = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		return $contactos;
	}

	// MARK: STREAM CONTACTOS

	public function streamContactos(): void
	{
		$this->start();

		$lastPing = 0;

		$contactos = [];

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$contactosUpdate = $this->obtainContactos();

			if ($contactosUpdate !== $contactos) {
				$this->sendEvent('new update', $contactosUpdate);
				$contactos = $contactosUpdate;
			}

			if (time() - $lastPing > 10) {
				$this->keepAlive();
				$lastPing = time();
			}

			usleep(300000); // 0.3s
		}
	}
}
