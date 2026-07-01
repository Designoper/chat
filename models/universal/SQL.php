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
	private const string BASE32_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUV';

	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: GENERATE ULID

	// protected function generateUlid(): string
	// {
	// 	$time = microtime(true) * 1000;
	// 	$time = base_convert((string) (int) $time, 10, 32);
	// 	$time = str_pad($time, 10, '0', STR_PAD_LEFT);

	// 	$rand = '';
	// 	for ($i = 0; $i < 16; $i++) {
	// 		$rand .= base_convert((string) random_int(0, 31), 10, 32);
	// 	}

	// 	return strtoupper($time . $rand);
	// }

	protected function generateUlid(): string
	{
		// 1. Tiempo en milisegundos → entero
		$time = (string) (int) (microtime(true) * 1000);

		// 2. Convertir a base32 (Crockford) — base_convert requiere string
		$time32 = strtoupper(str_pad(base_convert($time, 10, 32), 10, '0', STR_PAD_LEFT));

		// 3. Generar 16 caracteres aleatorios en base32
		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= self::BASE32_ALPHABET[random_int(0, 31)];
		}

		$ulid = $time32 . $rand;

		return $ulid;
	}

	protected function generateUlidAdvanced(): string
	{
		$time = (int) (microtime(true) * 1000);
		$time32 = $this->encodeBase32($time, 10);

		$rand = '';
		for ($i = 0; $i < 16; $i++) {
			$rand .= self::BASE32_ALPHABET[random_int(0, 31)];
		}

		return $time32 . $rand;
	}

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
