<?php

declare(strict_types=1);

abstract readonly class Response
{
    protected int $status;
    protected string|array $content;
    protected ArrayObject $validationErrors;
    protected ArrayObject $integrityErrors;
    private string|array $response;

    protected function __construct()
    {
        $this->validationErrors = new ArrayObject();
        $this->integrityErrors = new ArrayObject();
    }

    // ============================================================================
    // MARK: BUILD RESPONSE
    // ============================================================================
    private function buildResponse(): void
    {
        if ($this->validationErrors->count() > 0) {
            $this->response = $this->validationErrors->getArrayCopy();
            return;
        }

        if ($this->integrityErrors->count() > 0) {
            $this->response = $this->integrityErrors->getArrayCopy();
            return;
        }

        if (isset($this->content)) {
            $this->response = $this->content;
            return;
        }

        $this->response = [];
    }

    // ============================================================================
    // MARK: SEND OK RESPONSE
    // ============================================================================
    protected function sendOkResponse(int $http_code, string|array|null $content = null): never
    {
        $this->status = $http_code;

        if ($content) {
            $this->content = $content;
        }

        $this->buildResponse();

        http_response_code($this->status);
        header("Content-Type: application/json; charset=utf-8");

        if (isset($this->response)) {
            echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        exit;
    }

    // ============================================================================
    // MARK: SEND NOT OK RESPONSE
    // ============================================================================
    protected function sendNotOkResponse(): never
    {
        $this->buildResponse();

        http_response_code($this->status);
        header("Content-Type: application/json; charset=utf-8");

        if (isset($this->response)) {
            echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        exit;
    }

    // ============================================================================
    // MARK: CHECK VALIDATION ERRORS
    // ============================================================================
    protected function checkValidationErrors(): void
    {
        if ($this->validationErrors->count() > 0) {
            $this->status = 400;
            $this->sendNotOkResponse();
        }
    }

    // ============================================================================
    // MARK: CHECK INTEGRITY ERRORS
    // ============================================================================
    protected function checkIntegrityErrors(): void
    {
        if ($this->integrityErrors->count() > 0) {
            $this->sendNotOkResponse();
        }
    }

    // ============================================================================
    // MARK: INTEGRITY ERROR SETUP
    // ============================================================================
    protected function integrityErrorSetup(int $http_code, string $error_message): void
    {
        $this->status = $http_code;
        $this->integrityErrors->append($error_message);
        $this->checkIntegrityErrors();
    }
}
