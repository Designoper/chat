<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

abstract readonly class Auth extends Database
{
	protected ?string $session_ulid;

	protected function __construct()
	{
		parent::__construct();

		session_start();
		$this->session_ulid = $_SESSION['ulid_usuario'] ?? null;
		session_write_close();
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
}
