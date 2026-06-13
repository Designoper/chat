<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/Helper.php';

final readonly class Usuario extends Helper
{
	protected string $nombre_usuario;
	protected string $password;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: READ USUARIOS

	public function readUsuarios(): void
	{
		$this->authEndpoint();

		$id_usuario = $this->session_user;

		$statement =
			"SELECT id_usuario, nombre_usuario
			 FROM usuarios
			 WHERE id_usuario != ?
			 ORDER BY nombre_usuario ASC";

		$usuarios = $this->sqlArray(
			$statement,
			'i',
			[
				$id_usuario
			]
		);

		$this->status = 200;
		$this->content = $usuarios;
		$this->sendResponse();
	}

	// MARK: CREATE

	public function createUsuario(): void
	{
		$this->setNombre('nombre_usuario');
		$this->setPassword('password');

		$this->checkValidationErrors();

		$nombre_usuario = $this->nombre_usuario;
		$password = password_hash($this->password, PASSWORD_DEFAULT);

		try {
			$statement =
				"INSERT INTO usuarios (nombre_usuario, password)
            	VALUES (?, ?)";

			$id_usuario = $this->sqlId(
				$statement,
				'ss',
				[
					$nombre_usuario,
					$password
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

		$_SESSION['id_usuario'] = $id_usuario;

		$this->status = 201;
		$this->sendResponse();
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setNombre('nombre_usuario');
		$this->setPassword('password');

		$this->checkValidationErrors();

		$nombre_usuario = $this->nombre_usuario;
		$password = $this->password;

		$statement =
			"SELECT id_usuario, password
			FROM usuarios
			WHERE nombre_usuario = ?";

		$row = $this->sql2(
			$statement,
			's',
			[
				$nombre_usuario
			]
		);

		if (!$row || !password_verify($password, $row['password'])) {
			$this->status = 401;
			$this->errors->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['id_usuario'] = $row['id_usuario'];

		$this->status = 200;
		$this->sendResponse();
	}

	// MARK: LOGOUT

	public function logout(): void
	{
		$this->authEndpoint();

		// Asegurar que la sesión está iniciada
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$_SESSION = [];

		// Invalidar cookie de sesión si existe
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

		$id_usuario = $this->session_user;

		$statement =
			"SELECT id_usuario, nombre_usuario
			FROM usuarios
			WHERE id_usuario = ?";

		$usuario = $this->sqlArray(
			$statement,
			'i',
			[
				$id_usuario
			]
		);

		$this->status = 200;
		$this->content = $usuario;
		$this->sendResponse();
	}

	// MARK: DELETE

	public function deleteUsuario(): void
	{
		$this->authEndpoint();

		$id_usuario = $this->session_user;

		$statement =
			"DELETE FROM usuarios
			WHERE id_usuario = ?";

		$this->sqlDelete(
			$statement,
			'i',
			[
				$id_usuario
			]
		);

		$this->logout();
	}
}
