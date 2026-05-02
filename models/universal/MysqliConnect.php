<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';

abstract class MysqliConnect extends Sanitizer
{
	private readonly string $hostname;
	private readonly string $username;
	private readonly string $password;
	private readonly string $database;
	private readonly mysqli $connection;
	private readonly string $host;

	protected function __construct()
	{
		session_start();

		parent::__construct();

		$this->hostname = getenv('HOSTNAME');
		$this->username = getenv('USERNAME');
		$this->password = getenv('PASSWORD');
		$this->database = getenv('DATABASE');
		$this->setConnection();
		$this->setHost();
	}

	// MARK: GETTERS

	protected function getConnection(): mysqli
	{
		return $this->connection;
	}

	protected function getHost(): string
	{
		return $this->host;
	}

	protected function getAuthenticatedUserId(): ?int
	{
		return $_SESSION['id_usuario'] ?? null;
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
}
