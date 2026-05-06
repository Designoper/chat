<?php

declare(strict_types=1);

require_once __DIR__ . '/Response.php';

abstract readonly class MysqliConnect extends Response
{
	private string $hostname;
	private string $username;
	private string $password;
	private string $database;
	protected mysqli $connection;
	protected string $host;
	protected ?int $session_user;

	protected function __construct()
	{
		session_start();

		parent::__construct();

		$this->session_user = $_SESSION['id_usuario'] ?? null;

		$this->hostname = getenv('HOSTNAME');
		$this->username = getenv('USERNAME');
		$this->password = getenv('PASSWORD');
		$this->database = getenv('DATABASE');
		$this->setConnection();
		$this->setHost();
	}

	// MARK: SETTERS

	private function setConnection(): void
	{
		$this->connection = new mysqli(
			$this->hostname,
			$this->username,
			$this->password,
			$this->database
		);

		$this->connection->set_charset('utf8');
	}

	private function setHost(): void
	{
		$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$this->host = $protocol . '://' . $host;
	}

	// MARK: AUTHENTICATION

	public function authBrowser(): void
	{
		if ($this->session_user === null) {
			header("Location: index.html");
			exit;
		}
	}

	protected function authEndpoint(): void
	{
		if ($this->session_user === null) {
			$this->setStatus(401);
			$this->errors->setIntegrityError('No hay sesión');
		}
	}
}
