<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

enum SqlReturn
{
	case FetchAll;
	case FetchAssoc;
	case FetchColumn;
	case Boolean;
}

abstract readonly class SQL extends Database
{
	protected const string ISO8601_SQL_FORMAT = "'%Y-%m-%dT%H:%i:%sZ'";
	private const string BASE32_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: GENERATE ULID

	protected function generateUlid(): string
	{
		$time = (int) (microtime(true) * 1000);
		$time32 = $this->encodeBase32($time, 10);

		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= self::BASE32_ALPHABET[random_int(0, 31)];
		}

		$ulid = $time32 . $rand;

		return $ulid;
	}

	// MARK: ENCODE BASE 32

	private function encodeBase32(int $value, int $length): string
	{
		$encoded = '';
		for ($i = 0; $i < $length; $i++) {
			$encoded = self::BASE32_ALPHABET[$value & 31] . $encoded;
			$value >>= 5;
		}
		return $encoded;
	}

	// MARK: EXECUTE QUERY

	protected function executeQuery(string $query, string $types, array $variables, ?SqlReturn $type = null): string|int|float|array|null|bool
	{
		$mysqli_stmt = $this->connection->prepare($query);
		$mysqli_stmt->bind_param($types, ...$variables);
		$mysqli_stmt->execute();
		$resultSet = $mysqli_stmt->get_result();

		switch ($type) {
			case SqlReturn::FetchAll:
				$result = $resultSet->fetch_all(MYSQLI_ASSOC);
				break;

			case SqlReturn::FetchAssoc:
				$result = $resultSet->fetch_assoc();
				break;

			case SqlReturn::FetchColumn:
				$result = $resultSet->fetch_column();
				break;

			case SqlReturn::Boolean:
				$result = $resultSet->fetch_column() ? true : false;
				break;

			default:
				$result = null;
		}

		$mysqli_stmt->close();
		return $result;
	}
}
