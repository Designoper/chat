<?php

declare(strict_types=1);

abstract readonly class Env
{
    protected function __construct()
    {
        $this->setEnvVariables();
    }

    private function setEnvVariables(): void
    {
        $file = __DIR__ . '/../../.env';

        if (!is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
