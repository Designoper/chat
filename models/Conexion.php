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
		if (isset($_POST['ulid_contacto'])) {
			$this->setConexionDirecta();
		}

		if (isset($_POST['ulid_grupo'])) {
			$this->setConexionGrupal();
		}
	}

	// MARK: SET CONEXION DIRECTA

	private function setConexionDirecta(): void
	{
		$this->setUlid('ulid_contacto');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO conexion_directa (ulid_usuario, ulid_contacto)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$params = [
			$this->session_ulid,
			$this->ulid_contacto
		];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(201);
	}

	// MARK: SET CONEXION GRUPAL

	private function setConexionGrupal(): void
	{
		$this->setUlid('ulid_grupo');
		$this->checkValidationErrors();

		$query =
			"INSERT INTO conexion_grupal (ulid_usuario, ulid_grupo)
			VALUES (?, ?)
			ON DUPLICATE KEY
			UPDATE last_seen = CURRENT_TIMESTAMP";

		$params = [
			$this->session_ulid,
			$this->ulid_grupo
		];

		$this->executeQuery($query, $params);
		$this->sendOkResponse(201);
	}

	// MARK: GET CONEXION DIRECTA

	private function getConexionDirecta(): array
	{
		$query =
			"SELECT nombre_usuario,
				COALESCE(DATE_FORMAT(conexion_directa.last_seen, '%Y-%m-%dT%H:%i:%sZ'), 0) AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_directa.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_directa
				ON usuarios.ulid_usuario = conexion_directa.ulid_usuario
			WHERE conexion_directa.ulid_usuario = ?
			AND conexion_directa.ulid_contacto = ?";

		// $params = [
		// 	$this->session_ulid,
		// 	$this->ulid_contacto
		// ];

		$conexion = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		return $conexion;
	}

	// MARK: GET CONEXION GRUPAL

	private function getConexionGrupal(): array
	{
		$query =
			"SELECT usuarios.nombre_usuario,
				COALESCE(DATE_FORMAT(conexion_grupal.last_seen, '%Y-%m-%dT%H:%i:%sZ'), 0) AS last_seen,
				COALESCE(UNIX_TIMESTAMP(conexion_grupal.last_seen), 0) AS last_seen_unix
			FROM usuarios
			LEFT JOIN conexion_grupal
				ON usuarios.ulid_usuario = conexion_grupal.ulid_usuario
			LEFT JOIN membresias
				ON usuarios.ulid_usuario = membresias.ulid_usuario
			WHERE membresias.ulid_grupo = ?
			AND membresias.ulid_usuario != ?
			AND membresias.rol IN ('fundador','miembro')
			ORDER BY usuarios.nombre_usuario ASC";

		$params = [
			$this->ulid_grupo,
			$this->session_ulid
		];

		$conexion = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		return $conexion;
	}

	// MARK: STREAM CONEXION

	public function streamConexion(): void
	{
		if (isset($_GET['ulid_contacto'])) {
			$this->setUlid('ulid_contacto');
			$getConexion = fn() => $this->getConexionDirecta();
			$tipo = "directo";
		}

		if (isset($_GET['ulid_grupo'])) {
			$this->setUlid('ulid_grupo');
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
