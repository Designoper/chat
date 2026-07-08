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

	protected function __construct()
	{
		parent::__construct();

		$this->hostname = getenv('HOSTNAME');
		$this->username = getenv('USERNAME');
		$this->password = getenv('PASSWORD');
		$this->database = getenv('DATABASE');
		$this->setConnection();
	}

	// ============================================================================
	// MARK: SET CONNECTION
	// ============================================================================
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

			$this->connection->set_charset('utf8mb4');
		} catch (mysqli_sql_exception $e) {
			$this->integrityErrorSetup(500, "Error({$e->getCode()}): {$e->getMessage()}");
		}
	}
}
