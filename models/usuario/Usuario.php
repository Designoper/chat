<?php

declare(strict_types=1);

require_once __DIR__ . '/UsuarioIntegrityErrors.php';

final class Usuario extends UsuarioIntegrityErrors
{
	private readonly string $usuario;
	private readonly string $password;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getUsuario(): string
	{
		return $this->usuario;
	}

	private function getPassword(): string
	{
		return $this->password;
	}

	// MARK: SETTERS

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

		if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $value)) {
			$this->setValidationError("El campo 'password' debe tener como mínimo 8 carácteres, de los cuales 1 debe ser minúscula, 1 mayúscula y 1 número.");
			return;
		}

		$this->password = $value;
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setUsuario();
		$this->setPassword();

		$this->checkValidationErrors();

		$this->checkIntegrityErrors();

		$statement = "SELECT *
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

		$usuario = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if (!$usuario) {
			$this->setStatus(401);
			$this->setMessage("Credenciales inválidas");
			$this->getResponse();
			exit();
		}
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

		$this->setStatus(201);
		$this->setMessage("Usuario creado con éxito");
		$this->setContent([
			"id_usuario" => $id_usuario,
		]);
		$this->getResponse();
	}
}
