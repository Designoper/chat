<?php

declare(strict_types=1);

require_once __DIR__ . '/UsuarioIntegrityErrors.php';

final class Usuario extends UsuarioIntegrityErrors
{
	private readonly int $id_usuario;
	private readonly string $usuario;
	private readonly string $password;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getIdUsuario(): int
	{
		return $this->id_usuario;
	}

	private function getUsuario(): string
	{
		return $this->usuario;
	}

	private function getPassword(): string
	{
		return $this->password;
	}

	// MARK: SETTERS

	private function setIdUsuario(): void
	{
		$value = $_SESSION['id_usuario'] ?? null;
		$this->id_usuario = $value;
	}

	private function setUsuario(): void
	{
		$value = $_POST['usuario'] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo 'usuario' no puede estar vacío.");
			return;
		}

		$this->usuario = $value;
	}

	private function setPassword(): void
	{
		$value = $_POST['password'] ?? null;

		if (empty($value)) {
			$this->setValidationError("El campo 'password' no puede estar vacío.");
			return;
		}

		// if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $value)) {
		// 	$this->setValidationError("El campo 'password' debe tener como mínimo 8 carácteres, de los cuales 1 debe ser minúscula, 1 mayúscula y 1 número.");
		// 	return;
		// }

		$this->password = $value;
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setUsuario();
		$this->setPassword();

		$this->checkValidationErrors();

		$statement = "SELECT id_usuario
		FROM usuarios
		WHERE nombre = ?
		AND PASSWORD = ?";

		$query = $this->getConnection()->prepare($statement);

		$usuario = $this->getUsuario();
		$password = $this->getPassword();

		$query->bind_param(
			"ss",
			$usuario,
			$password
		);

		$query->execute();

		$id_usuario = $query->get_result()->fetch_column(0);

		$query->close();

		if (!$id_usuario) {
			$this->setStatus(401);
			$this->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['id_usuario'] = $id_usuario;

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

		$this->setStatus(200);
		$this->setMessage("Logout exitoso");
		$this->getResponse();
	}

	// MARK: CREATE

	public function createUsuario(): void
	{
		$this->setUsuario();
		$this->setPassword();

		$this->checkValidationErrors();

		$usuario = $this->getUsuario();
		$password = $this->getPassword();

		$this->nombreUsuarioExists($usuario);

		$this->checkIntegrityErrors();

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

		$_SESSION['id_usuario'] = $id_usuario;

		$this->setStatus(201);
		$this->setMessage("Usuario creado con éxito");
		$this->getResponse();
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
}
