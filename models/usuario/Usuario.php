<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

class Usuario extends ApiResponse
{
	private readonly ?int $id_usuario;
	private readonly string $usuario;
	private readonly string $password;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: SETTERS

	private function setIdUsuario(): void
	{
		$name = 'id_usuario';
		$value = $_SESSION[$name] ?? null;
		$this->id_usuario = $value;
	}

	private function setUsuario(): void
	{
		$name = 'usuario';
		$value = $_POST[$name] ?? null;

		empty($value)
			? $this->setValidationError("El campo $name no puede estar vacío.")
			: $this->usuario = $value;
	}

	private function setPasswordHashed(): void
	{
		$name = 'password';
		$value = $_POST[$name] ?? null;

		empty($value)
			? $this->setValidationError("El campo $name no puede estar vacío.")
			: $this->password = password_hash($value, PASSWORD_DEFAULT);
	}

	private function setPasswordPlain(): void
	{
		$name = 'password';
		$value = $_POST[$name] ?? null;

		empty($value)
			? $this->setValidationError("El campo $name no puede estar vacío.")
			: $this->password = $value;
	}

	// MARK: READ

	public function readUsuarios(): void
	{
		$statement =
			"SELECT id_usuario, nombre
			 FROM usuarios
			 WHERE id_usuario != ?
			 ORDER BY nombre ASC";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$_SESSION['id_usuario']
		);

		$query->execute();

		$usuarios = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$usuarios
			? 'Usuarios obtenidos.'
			: 'No hay ningún usuario.';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($usuarios);
		$this->getResponse();
	}

	// public function readUsuario()
	// {
	// 	$statement =
	// 		"SELECT nombre
	// 		 FROM usuarios
	// 		 WHERE id_usuario = ?";

	// 	$query = $this->getConnection()->prepare($statement);

	// 	$query->bind_param(
	// 		"i",
	// 		$_SESSION['id_usuario']
	// 	);

	// 	$query->execute();

	// 	$usuario = $query->get_result()->fetch_all(MYSQLI_ASSOC);
	// 	$message =
	// 		$usuario
	// 		? 'Usuario obtenido.'
	// 		: 'No hay ningún usuario.';

	// 	$query->close();

	// 	$this->setStatus(200);
	// 	$this->setMessage($message);
	// 	$this->setContent($usuario);
	// 	$this->getResponse();
	// }

	// MARK: CREATE

	public function createUsuario(): void
	{
		$this->setUsuario();
		$this->setPasswordHashed();

		$this->checkValidationErrors();

		$usuario = $this->usuario;
		$password = $this->password;

		try {
			$statement =
				"INSERT INTO usuarios (nombre, password)
            VALUES (?, ?)";

			$query = $this->getConnection()->prepare($statement);

			$query->bind_param(
				"ss",
				$usuario,
				$password
			);

			$query->execute();

			$id_usuario = $query->insert_id;
			$query->close();
		} catch (\mysqli_sql_exception $error) {

			if ($error->getCode() === 1062) {
				$this->setStatus(409);
				$this->setIntegrityError('¡Este nombre de usuario ya existe!');
				$this->checkIntegrityErrors();
			}

			throw $error;
		}

		$_SESSION['id_usuario'] = $id_usuario;

		$this->setStatus(201);
		$this->setMessage("Usuario creado con éxito");
		$this->getResponse();
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setUsuario();
		$this->setPasswordPlain();

		$this->checkValidationErrors();

		$statement =
			"SELECT id_usuario, password
			FROM usuarios
			WHERE nombre = ?";

		$query = $this->getConnection()->prepare($statement);

		$usuario = $this->usuario;
		$passwordIngresada = $this->password;

		$query->bind_param(
			"s",
			$usuario
		);

		$query->execute();

		$result = $query->get_result();
		$row = $result->fetch_assoc();

		$query->close();

		if (!$row) {
			$this->setStatus(401);
			$this->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$hashGuardado = $row['password'];

		if (!password_verify($passwordIngresada, $hashGuardado)) {
			$this->setStatus(401);
			$this->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['id_usuario'] = $row['id_usuario'];

		$this->setStatus(200);
		$this->setMessage("Login exitoso");
		$this->getResponse();
	}

	// MARK: LOGOUT

	public function logout(): void
	{
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

		$this->setStatus(204);
		$this->getResponse();
	}

	// MARK: AUTH

	public function auth(): void
	{
		$this->setIdUsuario();
		$id_usuario = $this->id_usuario;

		if ($id_usuario === null) {
			header("Location: index.html");
			exit;
		}
	}

	// MARK: CURRENT

	public function currentUsuario(): void
	{
		if (!isset($_SESSION['id_usuario'])) {
			$this->setStatus(401);
			$this->setMessage("No hay usuario identificado");
			$this->getResponse();
		}

		$this->setStatus(200);
		$this->setMessage("Usuario identificado");
		$this->setContent([
			"id_usuario" => $_SESSION['id_usuario'],
		]);
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteUsuario(): void
	{
		$this->setIdUsuario();
		$id_usuario = $this->id_usuario;

		$statement =
			"DELETE FROM usuarios
			WHERE id_usuario = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();
		$query->close();

		unset($_SESSION['id_usuario']);

		$this->setStatus(204);
		$this->getResponse();
	}
}
