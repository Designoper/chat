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
	protected string $domain;
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
		$this->setDomain();
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

	private function setDomain(): void
	{
		$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$this->domain = $protocol . '://' . $host;
	}

	// MARK: AUTHENTICATION

	public function authBrowser(): void
	{
		if ($this->session_user === null) {
			header("Location: index.php");
			exit;
		}
	}

	public function sessionRedirect(): void
	{
		if ($this->session_user !== null) {
			header("Location: sala-principal.php");
			exit;
		}
	}

	protected function authEndpoint(): void
	{
		if ($this->session_user === null) {
			$this->status = 401;
			$this->errors->setIntegrityError('No hay sesión');
			$this->checkIntegrityErrors();
		}
	}
}
