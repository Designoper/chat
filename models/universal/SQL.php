<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

enum SqlReturn
{
	case FetchAll;
	case FetchAssoc;
	case FetchColumn;
	case Exists;
}

abstract readonly class SQL extends Database
{
	protected const string ISO8601_SQL_FORMAT = "'%Y-%m-%dT%H:%i:%sZ'";
	private const string BASE32_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

	protected function __construct()
	{
		parent::__construct();
	}

	// ============================================================================
	// MARK: ENCODE BASE 32
	// ============================================================================
	private function encodeBase32(int $value): string
	{
		$encoded = '';
		for ($i = 0; $i < 10; $i++) {
			$encoded = self::BASE32_ALPHABET[$value & 31] . $encoded;
			$value >>= 5;
		}
		return $encoded;
	}

	// ============================================================================
	// MARK: GENERATE ULID
	// ============================================================================
	protected function generateUlid(): string
	{
		$time = (int) (microtime(true) * 1000);
		$time32 = $this->encodeBase32($time);

		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= self::BASE32_ALPHABET[random_int(0, 31)];
		}

		$ulid = $time32 . $rand;

		return $ulid;
	}

	// ============================================================================
	// MARK: EXECUTE QUERY
	// ============================================================================
	protected function executeQuery(string $query, array $params, ?SqlReturn $type = null): string|int|float|array|bool|null
	{
		$types = implode('', array_column($params, 0));
		$values = array_column($params, 1);

		$mysqli_stmt = $this->connection->prepare($query);
		$mysqli_stmt->bind_param($types, ...$values);
		$mysqli_stmt->execute();
		$mysqli_result = $mysqli_stmt->get_result();

		switch ($type) {
			case SqlReturn::FetchAll:
				$result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
				break;

			case SqlReturn::FetchAssoc:
				$result = $mysqli_result->fetch_assoc();
				break;

			case SqlReturn::FetchColumn:
				$result = $mysqli_result->fetch_column();
				break;

			case SqlReturn::Exists:
				$result = (bool) $mysqli_result->fetch_row()[0];
				break;

			default:
				$result = null;
		}

		$mysqli_stmt->close();
		return $result;
	}
}
