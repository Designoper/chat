<?php

declare(strict_types=1);

require_once __DIR__ . '/SSE.php';

abstract readonly class Database extends SSE
{
	private string $hostname;
	private string $username;
	private string $password;
	private string $database;
	protected mysqli $connection;
	protected ?string $session_ulid;

	protected function __construct()
	{
		session_start();

		parent::__construct();

		$this->session_ulid = $_SESSION['ulid_usuario'] ?? null;

		$this->hostname = getenv('HOSTNAME');
		$this->username = getenv('USERNAME');
		$this->password = getenv('PASSWORD');
		$this->database = getenv('DATABASE');
		$this->setConnection();
	}

	// MARK: SET CONNECTION

	private function setConnection(): void
	{
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {
			$this->connection = @new mysqli(
				$this->hostname,
				$this->username,
				$this->password,
				$this->database
			);

			$this->connection->set_charset('utf8');
		} catch (mysqli_sql_exception $e) {
			$this->status = 500;
			$this->errors->setIntegrityError("Error({$e->getCode()}): {$e->getMessage()}");
			$this->checkIntegrityErrors();
		}
	}

	// MARK: AUTH BROWSER

	public function authBrowser(): void
	{
		if ($this->session_ulid === null) {
			header("Location: index.php");
			exit;
		}
	}

	// MARK: SESSION REDIRECT

	public function sessionRedirect(): void
	{
		if ($this->session_ulid !== null) {
			header("Location: sala-principal.php");
			exit;
		}
	}

	// MARK: AUTH ENDPOINT

	protected function authEndpoint(): void
	{
		if ($this->session_ulid === null) {
			$this->status = 401;
			$this->errors->setIntegrityError('No hay sesión');
			$this->checkIntegrityErrors();
		}
	}
}
