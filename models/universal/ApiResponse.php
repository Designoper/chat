<?php

declare(strict_types=1);

require_once __DIR__ . '/MysqliConnect.php';

abstract class ApiResponse extends MysqliConnect
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

    private function getStatus(): int
    {
        return $this->status;
    }

    private function getMessage(): string
    {
        return $this->message;
    }

    private function getContent(): array
    {
        return $this->content;
    }

    private function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    private function getIntegrityErrors(): array
    {
        return $this->integrityErrors;
    }

    protected function getResponse(): never
    {
        http_response_code($this->getStatus());
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
        $this->response['message'] = $this->getMessage();
    }

    protected function setContent(array $content): void
    {
        $this->content = $content;
        $this->response['content'] = $this->getContent();
    }

    protected function setValidationError(string $validationError): void
    {
        $this->validationErrors[] = $validationError;
    }

    private function setValidationErrors(): void
    {
        $this->response['validationErrors'] = $this->getValidationErrors();
    }

    protected function setIntegrityError(string $integrityError): void
    {
        $this->integrityErrors[] = $integrityError;
    }

    private function setIntegrityErrors(): void
    {
        $this->response['integrityErrors'] = $this->getIntegrityErrors();
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->getValidationErrors())) {
            $this->setStatus(400);
            $this->setMessage("Hay errores de validación");
            $this->setValidationErrors();
            $this->getResponse();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->getIntegrityErrors())) {
            $this->setMessage("Hay errores de integridad");
            $this->setIntegrityErrors();
            $this->getResponse();
        }
    }

    protected function invalidUser(): void
    {
        $this->setStatus(401);
        $this->setMessage("Credenciales incorrectas");
        $this->getResponse();
    }
}
