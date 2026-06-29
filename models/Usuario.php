<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Setter.php';

readonly class Usuario extends Setter
{
	protected string $nombre_usuario;
	protected string $password;
	protected string $codigo_contacto;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GENERAR CÓDIGO

	private function generarCodigo($length = 6)
	{
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $chars[random_int(0, strlen($chars) - 1)];
		}
		return $code;
	}

	// MARK: CREATE

	public function createUsuario(): void
	{
		$this->setNombre('nombre_usuario');
		$this->setPassword('password');

		$this->checkValidationErrors();

		$query =
			"INSERT INTO usuarios (nombre_usuario, password, codigo_contacto)
        	VALUES (?, ?, ?)";

		$maxIntentos = 5;
		$intento = 0;

		while ($intento < $maxIntentos) {

			$codigo = $this->generarCodigo();

			try {
				$id_usuario = $this->executeQuery(
					$query,
					'sss',
					[
						$this->nombre_usuario,
						password_hash($this->password, PASSWORD_DEFAULT),
						$codigo
					],
					SqlReturn::InsertId
				);

				$_SESSION['id_usuario'] = $id_usuario;
				$this->status = 201;
				$this->sendResponse();
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

				throw $error;
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
			"SELECT id_usuario, password
			FROM usuarios
			WHERE nombre_usuario = ?";

		$usuario = $this->executeQuery(
			$query,
			's',
			[
				$this->nombre_usuario
			],
			SqlReturn::FetchAssoc
		);

		if (!$usuario || !password_verify($this->password, $usuario['password'])) {
			$this->status = 401;
			$this->errors->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['id_usuario'] = $usuario['id_usuario'];

		$this->status = 200;
		$this->sendResponse();
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

		$this->status = 204;
		$this->sendResponse();
	}

	// MARK: CURRENT

	public function currentUsuario(): void
	{
		$this->authEndpoint();

		$query =
			"SELECT id_usuario, nombre_usuario, codigo_contacto
			FROM usuarios
			WHERE id_usuario = ?";

		$usuario = $this->executeQuery(
			$query,
			'i',
			[
				$this->session_user
			],
			SqlReturn::FetchAssoc
		);

		$this->status = 200;
		$this->content = $usuario;
		$this->sendResponse();
	}

	// MARK: DELETE

	public function deleteUsuario(): void
	{
		$this->authEndpoint();

		$query =
			"DELETE FROM usuarios
			WHERE id_usuario = ?";

		$this->executeQuery(
			$query,
			'i',
			[
				$this->session_user
			]
		);

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
			WHERE id_usuario = ?";

		try {
			$this->executeQuery(
				$query,
				'si',
				[
					$this->nombre_usuario,
					$this->session_user
				]
			);
		} catch (\mysqli_sql_exception $error) {

			if ($error->getCode() === 1062) {
				$this->status = 409;
				$this->errors->setIntegrityError('¡Este nombre de usuario ya existe!');
				$this->checkIntegrityErrors();
			}

			throw $error;
		}

		$this->status = 201;
		$this->sendResponse();
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
			WHERE id_usuario = ?";

		$this->executeQuery(
			$query,
			'si',
			[
				password_hash($this->password, PASSWORD_DEFAULT),
				$this->session_user
			]
		);

		$this->status = 201;
		$this->sendResponse();
	}
}
