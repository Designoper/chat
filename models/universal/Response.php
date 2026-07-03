<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/ErrorHandler.php';

abstract readonly class Response extends Sanitizer
{
    protected int $status;
    protected array $content;
    protected ErrorHandler $errors;
    private array $response;

    protected function __construct()
    {
        parent::__construct();
        $this->errors = new ErrorHandler();
    }

    // MARK: SEND RESPONSE

    protected function sendResponse(): never
    {
        $this->buildResponse();

        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // MARK: BUILD RESPONSE

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

    // MARK: CHECK VALIDATIONS ERRORS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->status = 400;
            $this->sendResponse();
        }
    }

    // MARK: CHECK INTEGRITY ERRORS

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->errors->getIntegrityErrors())) {
            $this->sendResponse();
        }
    }
}
