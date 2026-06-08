<?php

declare(strict_types=1);

require_once __DIR__ . '/universal/MysqliConnect.php';

final readonly class Usuario extends MysqliConnect
{
	private ?int $id_usuario;
	private string $nombre_usuario;
	private string $password;

	public function __construct()
	{
		parent::__construct();

		$this->id_usuario = $this->session_user;
	}

	// MARK: SETTERS

	protected function setNombre(string $name): void
	{
		$value = $_POST[$name] ?? null;
		$error_message = "El nombre solo puede contener mayúsculas(A-Z), minúsculas(a-z), números(1-9) y guión bajo (_). Longitud de 3 a 20 carácteres.";

		preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value)
			? $this->$name = $value
			: $this->errors->setValidationError($error_message);
	}

	private function setNombreUsuario(): void
	{
		$name = 'nombre_usuario';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->nombre_usuario = $value;
	}

	private function setPassword(): void
	{
		$name = 'password';
		$value = $_POST[$name] ?? null;
		$error_message = "El campo $name no puede estar vacío.";

		empty($value)
			? $this->errors->setValidationError($error_message)
			: $this->password = $value;
	}

	// MARK: READ CONTACTOS

	public function readContactos(): void
	{
		$contactos = $this->obtainContactos();

		$message =
			$contactos
			? 'Contactos obtenidos.'
			: 'No hay ningún contacto.';

		$this->status = 200;
		$this->message = $message;
		$this->content = $contactos;
		$this->sendResponse();
	}

	// MARK: OBTAIN CONTACTOS

	private function obtainContactos(): array
	{
		$this->authEndpoint();

		$id_usuario = $this->session_user;

		$statement =
			"SELECT
				u.id_usuario,
				u.nombre_usuario,
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
						OR (m2.id_emisor = u.id_usuario AND m2.id_receptor = ?)
					)
				)
			WHERE u.id_usuario != ?
			GROUP BY u.id_usuario, u.nombre_usuario, ult.fecha_envio, ult.contenido
			ORDER BY
				(ult.fecha_envio IS NULL) ASC,
				ult.fecha_envio DESC,
				u.nombre_usuario ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"iiiii",
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

	// MARK: READ USUARIOS

	public function readUsuarios(): void
	{
		$this->authEndpoint();

		$id_usuario = $this->id_usuario;

		$statement =
			"SELECT id_usuario, nombre_usuario
			 FROM usuarios
			 WHERE id_usuario != ?
			 ORDER BY nombre_usuario ASC";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();

		$usuarios = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$usuarios
			? 'Usuarios obtenidos.'
			: 'No hay ningún usuario.';

		$query->close();

		$this->status = 200;
		$this->message = $message;
		$this->content = $usuarios;
		$this->sendResponse();
	}

	// MARK: CREATE

	public function createUsuario(): void
	{
		$this->setNombreUsuario();
		// $this->setNombre('nombre_usuario');
		$this->setPassword();

		$this->checkValidationErrors();

		$nombre_usuario = $this->nombre_usuario;
		$password = password_hash($this->password, PASSWORD_DEFAULT);

		try {
			$statement =
				"INSERT INTO usuarios (nombre_usuario, password)
            	VALUES (?, ?)";

			$query = $this->connection->prepare($statement);

			$query->bind_param(
				"ss",
				$nombre_usuario,
				$password
			);

			$query->execute();

			$id_usuario = $query->insert_id;
			$query->close();
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
		$this->message = "Usuario creado con éxito";
		$this->sendResponse();
	}

	// MARK: LOGIN

	public function login(): void
	{
		$this->setNombreUsuario();
		$this->setPassword();

		$this->checkValidationErrors();

		$nombre_usuario = $this->nombre_usuario;
		$password = $this->password;

		$statement =
			"SELECT id_usuario, password
			FROM usuarios
			WHERE nombre_usuario = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"s",
			$nombre_usuario
		);

		$query->execute();

		$result = $query->get_result();
		$row = $result->fetch_assoc();

		$query->close();

		if (!$row) {
			$this->status = 401;
			$this->errors->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$hashGuardado = $row['password'];

		if (!password_verify($password, $hashGuardado)) {
			$this->status = 401;
			$this->errors->setIntegrityError("El usuario o la contraseña son incorrectos.");
			$this->checkIntegrityErrors();
		}

		$_SESSION['id_usuario'] = $row['id_usuario'];

		$this->status = 200;
		$this->message = "Login exitoso";
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

		$this->status = 200;
		$this->message = "Usuario identificado";
		$this->content = [
			"id_usuario" => $this->id_usuario,
		];
		$this->sendResponse();
	}

	// MARK: DELETE

	public function deleteUsuario(): void
	{
		$this->authEndpoint();

		$id_usuario = $this->id_usuario;

		$statement =
			"DELETE FROM usuarios
			WHERE id_usuario = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"i",
			$id_usuario
		);

		$query->execute();
		$query->close();

		$this->logout();
	}

	// MARK: STREAM CONTACTOS

	public function streamContactos(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		set_time_limit(0);
		ignore_user_abort(false);

		// Limpia buffers previos
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Headers SSE
		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
		header("Connection: keep-alive");
		header("X-Accel-Buffering: no");

		ini_set('output_buffering', 'off');
		ini_set('zlib.output_compression', 0);

		// Forzar flush inicial
		echo str_pad('', 4096) . "\n";
		flush();

		$lastPing = 0;

		$contactos = $this->obtainContactos();

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$contactosUpdate = $this->obtainContactos();

			if ($contactosUpdate !== $contactos) {

				echo "event: new update\n";
				echo "data: " . json_encode($contactosUpdate) . "\n\n";
				$contactos = $contactosUpdate;
			}

			if (time() - $lastPing > 10) {
				echo "event: ping\n";
				echo "data: keepalive\n\n";
				$lastPing = time();
			}

			@ob_flush();
			@flush();

			usleep(300000); // 0.3s
		}
	}
}
