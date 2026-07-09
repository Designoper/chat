<?php

declare(strict_types=1);

require_once __DIR__ . '/SSE.php';

abstract readonly class Database extends SSE
{
	private string $hostname;
	private string $username;
	private string $password;
	private string $database;
	protected PDO $connection;

	protected function __construct()
	{
		parent::__construct();

		// Nota de seguridad: getenv() puede fallar en entornos de producción específicos (como FPM).
		// Si notas que da vacío, es mejor usar la superglobal $_ENV['HOSTNAME'].
		$this->hostname = getenv('HOSTNAME') ?: '';
		$this->username = getenv('USERNAME') ?: '';
		$this->password = getenv('PASSWORD') ?: '';
		$this->database = getenv('DATABASE') ?: '';
		$this->setConnection();
	}

	// ============================================================================
	// MARK: SET CONNECTION
	// ============================================================================
	private function setConnection(): void
	{
		// 1. Definimos el DSN especificando directamente el charset utf8mb4
		$dsn = "mysql:host={$this->hostname};dbname={$this->database};charset=utf8mb4";

		// 2. Configuramos las banderas críticas de comportamiento de PDO
		$options = [
			// Fuerza a PDO a lanzar excepciones (PDOException) ante cualquier error de SQL
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

			// Desactiva la emulación. Obliga a MySQL a usar consultas preparadas reales a nivel binario
			PDO::ATTR_EMULATE_PREPARES => false,

			// Configura el modo de extracción por defecto a array asociativo
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		try {
			// 3. Instanciamos la conexión PDO pasándole los parámetros y opciones
			$this->connection = new PDO($dsn, $this->username, $this->password, $options);
		} catch (PDOException $e) {
			// Capturamos la excepción específica de PDO y sanitizamos el mensaje para producción
			$this->integrityErrorSetup(500, "Error de conexión al servidor de datos. Código: {$e->getCode()}");
		}
	}
}
