<?php

declare(strict_types=1);

require_once __DIR__ . '/Auth.php';

enum SqlReturn
{
	case FetchAll;
	case FetchAssoc;
	case FetchColumn;
	case Exists;
}

abstract readonly class SQL extends Auth
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
			$encoded = self::BASE32_ALPHABET[$value % 32] . $encoded;
			$value = intdiv($value, 32);
		}
		return $encoded;
	}

	// ============================================================================
	// MARK: GENERATE ULID
	// ============================================================================
	protected function generateUlid(): string
	{
		// 1. Evitar problemas de precisión con enteros de 64 bits y float
		$time = (int) floor(microtime(true) * 1000);
		$time32 = $this->encodeBase32($time);

		// 2. Generar aleatoriedad segura de golpe en lugar de un bucle lento
		$bytes = random_bytes(10);
		$rand = '';

		for ($i = 0; $i < 16; $i++) {
			$byteIndex = (int) ($i * 5 / 8);
			$bitOffset = ($i * 5) % 8;

			$byte1 = ord($bytes[$byteIndex]);
			$byte2 = isset($bytes[$byteIndex + 1]) ? ord($bytes[$byteIndex + 1]) : 0;

			$combined = ($byte1 << 8) | $byte2;
			$index = ($combined >> (11 - $bitOffset)) & 31;

			$rand .= self::BASE32_ALPHABET[$index];
		}

		return $time32 . $rand;
	}

	// ============================================================================
	// MARK: EXECUTE QUERY
	// ============================================================================
	public function executeQuery(string $query, array $params, ?SqlReturn $fetchMode = null): string|int|float|array|bool|null
	{
		// try {
		$stmt = $this->connection->prepare($query);
		$stmt->execute($params);

		return match ($fetchMode) {
			SqlReturn::FetchAssoc  => $stmt->fetch(PDO::FETCH_ASSOC),
			SqlReturn::FetchAll    => $stmt->fetchAll(PDO::FETCH_ASSOC),
			SqlReturn::FetchColumn => $stmt->fetchColumn(),
			SqlReturn::Exists      => (bool) $stmt->fetchColumn(),
			default                => null
		};
		// } catch (PDOException $e) {
		// 	// Aquí puedes centralizar el manejo de errores de base de datos de tu API
		// 	$this->integrityErrorSetup(500, "Error interno en la base de datos.");
		// }
	}
}
