<?php

declare(strict_types=1);

require_once __DIR__ . "/SSE.php";

abstract readonly class Database extends SSE
{
	private string $hostname;
	private string $username;
	private string $password;
	private string $database;
	private string $dsn;
	private const array OPTIONS = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	];
	protected PDO $connection;

	protected function __construct()
	{
		parent::__construct();

		$this->hostname = getenv("HOSTNAME");
		$this->username = getenv("USERNAME");
		$this->password = getenv("PASSWORD");
		$this->database = getenv("DATABASE");
		$this->dsn = "mysql:host={$this->hostname};dbname={$this->database};charset=utf8mb4";
		$this->setConnection();
	}

	// ============================================================================
	// MARK: SET CONNECTION
	// ============================================================================
	private function setConnection(): void
	{
		try {
			$this->connection = new PDO($this->dsn, $this->username, $this->password, self::OPTIONS);
		} catch (PDOException $error) {
			$this->integrityErrorSetup(500, "Error de conexión al servidor de datos. Código: {$error->getCode()}");
		}
	}
}
