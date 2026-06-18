<?php

declare(strict_types=1);

require_once __DIR__ . '/Env.php';

abstract readonly class Sanitizer extends Env
{
    protected function __construct()
    {
        parent::__construct();

        $this->sanitizeGlobals();
    }

    private function sanitizeValue(mixed $value): string|array
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value
        |> trim(...)
        |> stripslashes(...);

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function sanitizeGlobals(): void
    {
        $globals = ['_GET', '_POST', '_REQUEST', '_COOKIE'];
        foreach ($globals as $global) {
            if (isset($GLOBALS[$global]) && is_array($GLOBALS[$global])) {
                $GLOBALS[$global] = $this->sanitizeValue($GLOBALS[$global]);
            }
        }
    }
}
