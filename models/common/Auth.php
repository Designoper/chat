<?php

declare(strict_types=1);

require_once __DIR__ . "/Database.php";

abstract readonly class Auth extends Database
{
	protected ?string $session_ulid;

	protected const array SESSION_BASE_OPTIONS = [
		"cookie_lifetime" => 0,          // La cookie expira al cerrar el navegador
		"cookie_secure" => true,       // Solo se envía a través de HTTPS (Obligatorio)
		"cookie_httponly" => true,       // Bloquea el acceso a la cookie desde JavaScript (Evita XSS)
		"cookie_samesite" => 'Lax',      // Mitiga ataques CSRF (Cross-Site Request Forgery)
		"use_only_cookies" => true,      // Evita que el ID de sesión se pase por la URL
		"use_strict_mode" => true,        // Rechaza IDs de sesión inicializados por el usuario
	];

	// protected const array READ_ONLY_OPTIONS = [
	// 	'read_and_close'   => true        // Cierra el archivo tras leer
	// ];

	protected function __construct()
	{
		parent::__construct();

		$this->readSession();
	}

	// ============================================================================
	// MARK: READ SESSION
	// ============================================================================
	protected function readSession(): void
	{
		session_start(self::SESSION_BASE_OPTIONS);
		$this->session_ulid = $_SESSION["ulid_usuario"] ?? null;
		session_write_close();
	}

	// ============================================================================
	// MARK: WRITE SESSION
	// ============================================================================
	protected function writeSession(string $session_ulid): void
	{
		session_start(self::SESSION_BASE_OPTIONS);
		session_regenerate_id(true);
		$_SESSION["ulid_usuario"] = $session_ulid;
		session_write_close();
	}

	// ============================================================================
	// MARK: REGENERATE SESSION
	// ============================================================================
	protected function regenerateSession(): void
	{
		session_start(self::SESSION_BASE_OPTIONS);
		session_regenerate_id(true);
		session_write_close();
	}

	// ============================================================================
	// MARK: DESTROY SESSION
	// ============================================================================
	protected function destroySession(): void
	{
		session_start(self::SESSION_BASE_OPTIONS);

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
	}

	// ============================================================================
	// MARK: AUTH BROWSER
	// ============================================================================
	public function authBrowser(): void
	{
		if ($this->session_ulid === null) {
			header("Location: index.php");
			exit;
		}
	}

	// ============================================================================
	// MARK: SESSION REDIRECT
	// ============================================================================
	public function sessionRedirect(): void
	{
		if ($this->session_ulid !== null) {
			header("Location: sala-principal.php");
			exit;
		}
	}

	// ============================================================================
	// MARK: AUTH ENDPOINT
	// ============================================================================
	protected function authEndpoint(): void
	{
		if ($this->session_ulid === null) {
			$this->integrityErrorSetup(401, "No hay sesión.");
		}
	}

	// protected function authEndpoi(): void

	// {
	// 	session_start();

	// 	// ... después de verificar usuario y contraseña ...
	// 	$_SESSION['usuario_id'] = $usuario['id'];

	// 	// Obtener el ID de sesión actual de PHP
	// 	$sesion_actual = session_id();

	// 	// Guardar este ID en tu base de datos (ejemplo con PDO)
	// 	$stmt = $pdo->prepare("UPDATE usuarios SET session_id = ? WHERE id = ?");
	// 	$stmt->execute([$sesion_actual, $usuario['id']]);
	// }

	// protected function authEnd(): void
	// {
	// 	session_start();

	// 	// Verificar si el usuario ha iniciado sesión
	// 	if (!isset($_SESSION['usuario_id'])) {
	// 		header("Location: login.php");
	// 		exit;
	// 	}

	// 	// Obtener el Session ID registrado en la base de datos para este usuario
	// 	$stmt = $pdo->prepare("SELECT session_id FROM usuarios WHERE id = ?");
	// 	$stmt->execute([$_SESSION['usuario_id']]);
	// 	$usuario_db = $stmt->fetch();

	// 	// Comparar el ID de la base de datos con la sesión actual
	// 	if ($usuario_db['session_id'] !== session_id()) {
	// 		// La sesión actual ya no es válida (iniciaron sesión en otro dispositivo)
	// 		session_unset();     // Elimina las variables de sesión
	// 		session_destroy();   // Destruye el archivo de sesión en el servidor

	// 		// Redirigir al login con un mensaje
	// 		header("Location: login.php?error=sesion_duplicada");
	// 		exit;
	// 	}
	// }
}
