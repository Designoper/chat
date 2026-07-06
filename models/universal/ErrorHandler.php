<?php

declare(strict_types=1);

final class ErrorHandler
{
    private array $validationErrors = [];
    private array $integrityErrors = [];

    public function __construct() {}

    // ============================================================================
    // MARK: SET VALIDATION ERROR
    // ============================================================================
    public function setValidationError(string $message): void
    {
        $this->validationErrors[] = $message;
    }

    // ============================================================================
    // MARK: SET INTEGRITY ERROR
    // ============================================================================
    public function setIntegrityError(string $message): void
    {
        $this->integrityErrors[] = $message;
    }

    // ============================================================================
    // MARK: GET VALIDATION ERRORS
    // ============================================================================
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    // ============================================================================
    // MARK: GET INTEGRITY ERRORS
    // ============================================================================
    public function getIntegrityErrors(): array
    {
        return $this->integrityErrors;
    }
}
