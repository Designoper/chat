<?php

declare(strict_types=1);

require_once __DIR__ . "/Response.php";

abstract readonly class Env extends Response
{
    private const string ENV = ".env";

    protected function __construct()
    {
        parent::__construct();

        $this->setEnvVariables();
    }

    // ============================================================================
    // MARK: SET ENV VARIABLES
    // ============================================================================
    private function setEnvVariables(): void
    {
        $env_file = $_SERVER["DOCUMENT_ROOT"] . "/" . self::ENV;

        if (!is_readable($env_file)) {
            $this->integrityErrorSetup(500, "No se puede leer el archivo " . self::ENV);
        }

        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, "#")) {
                continue;
            }

            $parts = explode("=", $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $env_key = trim($parts[0]);
            $env_value = trim($parts[1]);
            putenv(sprintf("%s=%s", $env_key, $env_value));
        }
    }
}
