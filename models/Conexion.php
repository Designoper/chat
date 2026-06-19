<?php

declare(strict_types=1);

require_once __DIR__ . '/Mensaje.php';

final readonly class Conexion extends Mensaje
{
	public function __construct()
	{
		parent::__construct();
	}

	// MARK: SET CONEXION

	public function setConexion(): void
	{
		if (isset($_POST['id_receptor'])) {
			$this->setConexionDirecta();
		}

		if (isset($_POST['id_grupo'])) {
			$this->setConexionGrupal();
		}
	}

	// MARK: SET CONEXION DIRECTA

	private function setConexionDirecta(): void
	{
		$this->setId('id_receptor');
		$this->checkValidationErrors();

		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO conexion_directa (id_usuario, id_receptor)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$this->executeQuery(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_receptor,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: SET CONEXION GRUPAL

	private function setConexionGrupal(): void
	{
		$this->setId('id_grupo');
		$this->checkValidationErrors();

		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

		$statement =
			"INSERT INTO conexion_grupal (id_usuario, id_grupo)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$this->executeQuery(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_grupo,
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: GET CONEXION DIRECTA

	private function getConexionDirecta(): array
	{
		$id_receptor = $this->id_receptor;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT nombre_usuario,
				COALESCE(DATE_FORMAT(conexion_directa.last_seen, '%Y-%m-%dT%H:%i:%sZ'), 0) AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_directa.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_directa
				ON usuarios.id_usuario = conexion_directa.id_usuario
				AND conexion_directa.id_receptor = ?
			WHERE usuarios.id_usuario = ?";

		$conexion = $this->executeQuery(
			$statement,
			'ii',
			[
				$id_usuario,
				$id_receptor
			],
			SqlReturn::FetchAssoc
		);

		return $conexion;
	}

	// MARK: GET CONEXION GRUPAL

	private function getConexionGrupal(): array
	{
		$id_grupo = $this->id_grupo;
		$id_usuario = $this->session_user;

		$statement =
			"SELECT usuarios.nombre_usuario,
				COALESCE(DATE_FORMAT(conexion_grupal.last_seen, '%Y-%m-%dT%H:%i:%sZ'), 0) AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_grupal.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_grupal
				ON usuarios.id_usuario = conexion_grupal.id_usuario
			LEFT JOIN membresias
				ON usuarios.id_usuario = membresias.id_usuario
			WHERE membresias.id_grupo = ?
			AND membresias.id_usuario != ?
			AND membresias.rol IN ('fundador','miembro')
			ORDER BY usuarios.nombre_usuario ASC";

		$conexion = $this->executeQuery(
			$statement,
			'ii',
			[
				$id_grupo,
				$id_usuario
			],
			SqlReturn::FetchAssoc
		);

		return $conexion;
	}

	// MARK: STREAM CONEXION

	public function streamConexion(): void
	{
		if (isset($_GET['id_receptor'])) {
			$this->setId('id_receptor');
			$getConexion = fn() => $this->getConexionDirecta();
			$tipo = "directo";
		}

		if (isset($_GET['id_grupo'])) {
			$this->setId('id_grupo');
			$getConexion = fn() => $this->getConexionGrupal();
			$tipo = "grupal";
		}

		$this->checkValidationErrors();

		$this->setSSE();

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$conexion = $getConexion();

			static $array = [];

			$newArray = [];

			foreach ($conexion as $c) {
				$newArray[] = [
					'usuario' => $c['nombre_usuario'],
					'estado'  => (time() - $c['last_seen_unix'] > 10)
						? $c['last_seen']
						: 'Online'
				];
			}

			if ($newArray !== $array) {
				$this->sendEvent("conexion $tipo", $newArray);
				$array = $newArray;
			}

			$this->heartbeat();

			usleep(300000);
		}
	}
}
