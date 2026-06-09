<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/ErrorHandler.php';

abstract readonly class Response extends Sanitizer
{
    protected int $status;
    // protected string $message;
    protected array $content;
    protected ErrorHandler $errors;
    private array $response;

    protected function __construct()
    {
        parent::__construct();
        $this->errors = new ErrorHandler();
    }

    // MARK: GETTERS

    protected function sendResponse(): never
    {
        $this->buildResponse();

        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function buildResponse(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->response =
                // 'message' => $this->message,
                $this->errors->getValidationErrors();
            return;
        }

        if (!empty($this->errors->getIntegrityErrors())) {
            $this->response =
                // 'message' => $this->message,
                $this->errors->getIntegrityErrors();
            return;
        }

        if (isset($this->content)) {
            $this->response =
                // 'message' => $this->message,
                $this->content;
            return;
        }

        if ($this->status === 204) {
            $this->response = [];
            return;
        }

        $this->response = [];
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->status = 400;
            $this->sendResponse();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->errors->getIntegrityErrors())) {
            $this->sendResponse();
        }
    }
}
