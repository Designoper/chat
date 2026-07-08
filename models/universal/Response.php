<?php

declare(strict_types=1);

require_once __DIR__ . '/ErrorHandler.php';

abstract readonly class Response
{
    protected int $status;
    protected array $content;
    protected ErrorHandler $errors;
    private array $response;

    protected function __construct()
    {
        $this->errors = new ErrorHandler();
    }

    // ============================================================================
    // MARK: SEND NOT OK RESPONSE
    // ============================================================================
    protected function sendNotOkResponse(): never
    {
        $this->buildResponse();

        http_response_code($this->status);
        header('Content-Type: application/json');

        if (isset($this->response)) {
            echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        exit;
    }

    // ============================================================================
    // MARK: SEND OK RESPONSE
    // ============================================================================
    protected function sendOkResponse(int $http_code, ?array $content = null): never
    {
        $this->status = $http_code;

        if ($content) {
            $this->content = $content;
        }

        $this->buildResponse();

        http_response_code($this->status);
        header('Content-Type: application/json');

        if (isset($this->response)) {
            echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        exit;
    }

    // ============================================================================
    // MARK: BUILD RESPONSE
    // ============================================================================
    private function buildResponse(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->response = $this->errors->getValidationErrors();
            return;
        }

        if (!empty($this->errors->getIntegrityErrors())) {
            $this->response = $this->errors->getIntegrityErrors();
            return;
        }

        if (isset($this->content)) {
            $this->response = $this->content;
            return;
        }

        $this->response = [];
    }

    // ============================================================================
    // MARK: CHECK VALIDATION ERRORS
    // ============================================================================
    protected function checkValidationErrors(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->status = 400;
            $this->sendNotOkResponse();
        }
    }

    // ============================================================================
    // MARK: CHECK INTEGRITY ERRORS
    // ============================================================================
    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->errors->getIntegrityErrors())) {
            $this->sendNotOkResponse();
        }
    }

    // ============================================================================
    // MARK: CHECK INTEGRITY ERROR SETUP
    // ============================================================================
    protected function integrityErrorSetup(int $http_code, string $error_message): void
    {
        $this->status = $http_code;
        $this->errors->setIntegrityError($error_message);
        $this->checkIntegrityErrors();
    }
}
