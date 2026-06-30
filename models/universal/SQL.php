<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

enum SqlReturn
{
	case FetchAll;
	case FetchAssoc;
	case BindResult;
}

abstract readonly class SQL extends Database
{
	protected const string ISO8601_SQL_FORMAT = "'%Y-%m-%dT%H:%i:%sZ'";

	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: GENERATE ULID

	protected function generateUlid(): string
	{
		$time = microtime(true) * 1000;
		$time = base_convert((string) (int) $time, 10, 32);
		$time = str_pad($time, 10, '0', STR_PAD_LEFT);

		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= base_convert((string) random_int(0, 31), 10, 32);
		}

		return strtoupper($time . $rand);
	}

	// MARK: EXECUTE QUERY

	protected function executeQuery(string $query, string $types, array $variables, ?SqlReturn $type = null): string|int|array|null|false
	{
		$mysqli_stmt = $this->connection->prepare($query);
		$mysqli_stmt->bind_param($types, ...$variables);
		$mysqli_stmt->execute();

		switch ($type) {
			case SqlReturn::FetchAll:
				$result = $mysqli_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
				break;

			case SqlReturn::FetchAssoc:
				$result = $mysqli_stmt->get_result()->fetch_assoc();
				break;

			case SqlReturn::BindResult:
				$mysqli_stmt->bind_result($result);
				$mysqli_stmt->fetch();
				break;

			default:
				$result = null;
		}

		$mysqli_stmt->close();

		return $result;
	}
}
