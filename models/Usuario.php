<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Setter.php';

readonly class Usuario extends Setter
{
	protected string $nombre_usuario;
	protected string $password;
	protected string $ulid_usuario;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GENERAR CODIGO

	private function generarCodigo($length = 6)
	{
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $chars[random_int(0, strlen($chars) - 1)];
		}
		return $code;
	}

	// MARK: CREATE USUARIO

	public function createUsuario(): void
	{
		$this->setNombre('nombre_usuario');
		$this->setPassword('password');

		$this->checkValidationErrors();

		$this->ulid_usuario = $this->generateUlid();

		$query =
			"INSERT INTO usuarios (ulid_usuario, nombre_usuario, password, codigo_contacto)
        	VALUES (?, ?, ?, ?)";

		$maxIntentos = 5;
		$intento = 0;

		while ($intento < $maxIntentos) {

			$codigo = $this->generarCodigo();

			try {
				$this->executeQuery(
					$query,
					[
						['s', $this->ulid_usuario],
						['s', $this->nombre_usuario],
						['s', password_hash($this->password, PASSWORD_DEFAULT)],
						['s', $codigo]
					]
				);

				$_SESSION['ulid_usuario'] = $this->ulid_usuario;
				$this->sendOkResponse(201);
			} catch (\mysqli_sql_exception $error) {

				if ($error->getCode() === 1062 && str_contains($error->getMessage(), 'nombre_usuario')) {
					$this->status = 409;
					$this->errors->setIntegrityError('¡Este nombre de usuario ya existe!');
					$this->checkIntegrityErrors();
				}

				if ($error->getCode() === 1062 && str_contains($error->getMessage(), 'codigo_contacto')) {
					$intento++;
					continue;
				}
			}
		}

		$this->status = 500;
		$this->errors->setIntegrityError('No se pudo generar un código único. Inténtalo de nuevo.');
		$this->checkIntegrityErrors();
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setNombre('nombre_usuario');
		$this->setPassword('password');

		$this->checkValidationErrors();

		$query =
			"SELECT ulid_usuario, password
			FROM usuarios
			WHERE nombre_usuario = ?";

		$params = [['s', $this->nombre_usuario]];

		$usuario = $this->executeQuery($query, $params, SqlReturn::FetchAssoc);

		if (!$usuario || !password_verify($this->password, $usuario['password'])) {
			$this->status = 401;
			$this->errors->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['ulid_usuario'] = $usuario['ulid_usuario'];

		$this->sendOkResponse(200);
	}

	// MARK: LOGOUT

	public function logout(): void
	{
		$this->authEndpoint();

		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$_SESSION = [];

		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}

		session_destroy();

		$this->sendOkResponse(204);
	}

	// MARK: CURRENT USUARIO

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

	// MARK: DELETE USUARIO

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

	// MARK: CAMBIAR NOMBRE

	public function cambiarNombre(): void
	{
		$this->authEndpoint();

		$this->setNombre('nombre_usuario');
		$this->checkValidationErrors();

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
		} catch (\mysqli_sql_exception $error) {
			$this->isConflict($error, '¡Este nombre de usuario ya existe!');
		}

		$this->sendOkResponse(200);
	}

	// MARK: CAMBIAR PASSWORD

	public function cambiarPassword(): void
	{
		$this->authEndpoint();

		$this->setPassword('password');
		$this->checkValidationErrors();

		$query =
			"UPDATE usuarios
			SET password = ?
			WHERE ulid_usuario = ?";

		$params = [
			['s', password_hash($this->password, PASSWORD_DEFAULT)],
			['s', $this->session_ulid]
		];

		$this->executeQuery($query, $params);

		$this->sendOkResponse(200);
	}
}
