<?php

declare(strict_types=1);

require_once __DIR__ . '/Response.php';

abstract readonly class Env extends Response
{
    protected function __construct()
    {
        parent::__construct();
        $this->setEnvVariables();
    }

    // MARK: SET ENVIRONMENT VARIABLES

    private function setEnvVariables(): void
    {
        $env_file = $_SERVER['DOCUMENT_ROOT'] . '/.env';

        if (!is_readable($env_file)) {
            $this->status = 500;
            $this->errors->setIntegrityError('No se puede leer el archivo .env');
            $this->checkIntegrityErrors();
        }

        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            putenv(sprintf('%s=%s', $key, $value));
        }
    }
}
