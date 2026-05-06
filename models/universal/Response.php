<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';

abstract class Response extends Sanitizer
{
    private readonly int $status;
    private readonly string $message;
    private readonly array $content;
    private array $validationErrors;
    private readonly string $integrityErrors;
    private readonly array $response;

    protected function __construct()
    {
        parent::__construct();
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
        if (property_exists($this, 'validationErrors')) {
            $this->response = [
                'message' => $this->message,
                'validationErrors' => $this->validationErrors
            ];
            return;
        }

        if (property_exists($this, 'integrityErrors')) {
            $this->response = [
                'message' => $this->message,
                'integrityErrors' => $this->integrityErrors
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

    protected function setValidationError(string $validationError): void
    {
        $this->validationErrors[] = $validationError;
    }

    protected function setIntegrityError(string $integrityError): void
    {
        $this->integrityErrors = $integrityError;
        $this->checkIntegrityErrors();
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->validationErrors)) {
            $this->setStatus(400);
            $this->setMessage("Hay errores de validación");
            $this->getResponse();
        }
    }

    private function checkIntegrityErrors(): void
    {
        $this->setMessage("Hay errores de integridad");
        $this->getResponse();
    }
}
