<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/ErrorHandler.php';

abstract readonly class Response extends Sanitizer
{
    private int $status;
    private string $message;
    private array $content;
    protected ErrorHandler $errors;
    private array $response;

    protected function __construct()
    {
        parent::__construct();
        $this->errors = new ErrorHandler();
    }

    // MARK: GETTERS

    protected function getResponse(): never
    {
        $this->setResponse();

        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->response);
        exit();
    }

    private function setResponse(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->response = [
                'message' => $this->message,
                'validationErrors' => $this->errors->getValidationErrors()
            ];
            return;
        }

        if (!empty($this->errors->getIntegrityErrors())) {
            $this->response = [
                'message' => $this->message,
                'integrityErrors' => $this->errors->getIntegrityErrors()
            ];
            return;
        }

        if (property_exists($this, 'content')) {
            $this->response = [
                'message' => $this->message,
                'content' => $this->content
            ];
            return;
        }

        property_exists($this, 'message')
            ? $this->response = ['message' => $this->message]
            : $this->response = [];
    }

    // MARK: SETTERS

    protected function setStatus(int $status): void
    {
        $this->status = $status;
    }

    protected function setMessage(string $message): void
    {
        $this->message = $message;
    }

    protected function setContent(array $content): void
    {
        $this->content = $content;
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->setStatus(400);
            $this->setMessage("Hay errores de validación");
            $this->getResponse();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->errors->getIntegrityErrors())) {
            $this->setMessage("Hay errores de integridad");
            $this->getResponse();
        }
    }
}
