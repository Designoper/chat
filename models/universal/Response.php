<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';

abstract class Response extends Sanitizer
{
    private readonly int $status;
    private readonly string $message;
    private readonly array $content;
    private array $validationErrors = [];
    private array $integrityErrors = [];
    private array $response = [];

    protected function __construct()
    {
        parent::__construct();
    }

    // MARK: GETTERS

    protected function getResponse(): never
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->response);
        exit();
    }

    // MARK: SETTERS

    protected function setStatus(int $status): void
    {
        $this->status = $status;
    }

    protected function setMessage(string $message): void
    {
        $this->message = $message;
        $this->response['message'] = $this->message;
    }

    protected function setContent(array $content): void
    {
        $this->content = $content;
        $this->response['content'] = $this->content;
    }

    protected function setValidationError(string $validationError): void
    {
        $this->validationErrors[] = $validationError;
    }

    private function setValidationErrors(): void
    {
        $this->response['validationErrors'] = $this->validationErrors;
    }

    protected function setIntegrityError(string $integrityError): void
    {
        $this->integrityErrors[] = $integrityError;
    }

    private function setIntegrityErrors(): void
    {
        $this->response['integrityErrors'] = $this->integrityErrors;
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->validationErrors)) {
            $this->setStatus(400);
            $this->setMessage("Hay errores de validación");
            $this->setValidationErrors();
            $this->getResponse();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->integrityErrors)) {
            $this->setMessage("Hay errores de integridad");
            $this->setIntegrityErrors();
            $this->getResponse();
        }
    }
}
