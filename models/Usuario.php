<?php

declare(strict_types=1);

require_once __DIR__ . '/common/Request.php';

readonly class Usuario extends Request
{
	protected string $nombre_usuario;
	protected string $password;
	protected string $ulid_usuario;

	public function __construct()
	{
		parent::__construct();
	}

	// ============================================================================
	// MARK: GENERAR CODIGO
	// ============================================================================
	private function generarCodigo($length = 6)
	{
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $chars[random_int(0, strlen($chars) - 1)];
		}
		return $code;
	}

	// ============================================================================
	// MARK: CREATE USUARIO
	// ============================================================================
	public function createUsuario(): void
	{
		$this->setProperties([
			fn() => $this->setNombre('nombre_usuario'),
			fn() => $this->setPassword("password")
		]);

		$this->ulid_usuario = $this->generateUlid();

		$query =
			"INSERT INTO usuarios (ulid_usuario, nombre_usuario, password, codigo_contacto)
        	VALUES (?, ?, ?, ?)";

		$maxIntentos = 5;
		$intento = 0;

		while ($intento < $maxIntentos) {

			$codigo = $this->generarCodigo();

			$params = [
				['s', $this->ulid_usuario],
				['s', $this->nombre_usuario],
				['s', password_hash($this->password, PASSWORD_DEFAULT)],
				['s', $codigo]
			];

			try {
				$this->executeQuery($query, $params);
			} catch (mysqli_sql_exception $error) {

				if ($error->getCode() === 1062 && str_contains($error->getMessage(), 'nombre_usuario')) {
					$this->integrityErrorSetup(409, '¡Este nombre de usuario ya existe!');
				}

				if ($error->getCode() === 1062 && str_contains($error->getMessage(), 'codigo_contacto')) {
					$intento++;
					continue;
				}
			}

			$this->writeSession($this->ulid_usuario);
			$this->sendOkResponse(201);
		}

		$this->integrityErrorSetup(500, 'No se pudo generar un código único. Inténtalo de nuevo.');
	}

	// ============================================================================
	// MARK: LOGIN
	// ============================================================================
	public function login(): void
	{
		$this->setProperties([
			fn() => $this->setNombre('nombre_usuario'),
			fn() => $this->setPassword("password")
		]);

		$query =
			"SELECT ulid_usuario, password
			FROM usuarios
			WHERE nombre_usuario = ?";

		$params = [['s', $this->nombre_usuario]];

		$usuario = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		if (!$usuario || !password_verify($this->password, $usuario['password'])) {
			$this->integrityErrorSetup(401, "El usuario o la contraseña son incorrectos.");
		}

		$this->writeSession($usuario['ulid_usuario']);
		$this->sendOkResponse(200);
	}

	// ============================================================================
	// MARK: LOGOUT
	// ============================================================================
	public function logout(): void
	{
		$this->authEndpoint();
		$this->destroySession();
		$this->sendOkResponse(204);
	}

	// ============================================================================
	// MARK: CURRENT USUARIO
	// ============================================================================
	public function currentUsuario(): void
	{
		$this->authEndpoint();

		$query =
			"SELECT ulid_usuario, nombre_usuario, codigo_contacto
			FROM usuarios
			WHERE ulid_usuario = ?";

		$params = [['s', $this->session_ulid]];

		$usuario = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		$this->sendOkResponse(200, $usuario);
	}

	// ============================================================================
	// MARK: DELETE USUARIO
	// ============================================================================
	public function deleteUsuario(): void
	{
		$this->authEndpoint();

		$query =
			"DELETE FROM usuarios
			WHERE ulid_usuario = ?";

		$params = [['s', $this->session_ulid]];

		$this->executeQuery($query, $params);
		$this->logout();
	}

	// ============================================================================
	// MARK: CAMBIAR NOMBRE
	// ============================================================================
	public function cambiarNombre(): void
	{
		$this->authEndpoint();

		$this->setProperties([fn() => $this->setNombre("nombre_usuario")]);

		$query =
			"UPDATE usuarios
			SET nombre_usuario = ?
			WHERE ulid_usuario = ?";

		$params = [
			['s', $this->nombre_usuario],
			['s', $this->session_ulid]
		];

		try {
			$this->executeQuery($query, $params);
		} catch (mysqli_sql_exception $error) {
			if ($error->getCode() === 1062) {
				$this->integrityErrorSetup(409, "¡Este nombre de usuario ya existe!");
			}
		}

		$this->regenerateSession();
		$this->sendOkResponse(200);
	}

	// ============================================================================
	// MARK: CAMBIAR PASSWORD
	// ============================================================================
	public function cambiarPassword(): void
	{
		$this->authEndpoint();

		$this->setProperties([fn() => $this->setPassword("password")]);

		$query =
			"UPDATE usuarios
			SET password = ?
			WHERE ulid_usuario = ?";

		$params = [
			['s', password_hash($this->password, PASSWORD_DEFAULT)],
			['s', $this->session_ulid]
		];

		$this->executeQuery($query, $params);
		$this->regenerateSession();
		$this->sendOkResponse(200);
	}
}
