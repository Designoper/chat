<?php

declare(strict_types=1);

final class ErrorHandler
{
    private array $validationErrors = [];
    private array $integrityErrors = [];

    public function __construct() {}

    public function setValidationError(string $message): void
    {
        $this->validationErrors[] = $message;
    }

    public function setIntegrityError(string $message): void
    {
        $this->integrityErrors[] = $message;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function getIntegrityErrors(): array
    {
        return $this->integrityErrors;
    }
}
