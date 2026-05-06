<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/ErrorHandler.php';

abstract readonly class Response extends Sanitizer
{
    protected int $status;
    protected string $message;
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
        echo json_encode($this->response);
        exit();
    }

    private function buildResponse(): void
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

        if (property_exists($this, 'content') && !empty($this->content)) {
            $this->response = [
                'message' => $this->message,
                'content' => $this->content
            ];
            return;
        }

        // if (property_exists($this, 'content')) {
        //     $this->response = [
        //         'message' => $this->message,
        //         'content' => $this->content
        //     ];
        //     return;
        // }

        if (property_exists($this, 'status') && $this->status === 204) {
            $this->response = [];
            return;
        }

        property_exists($this, 'message')
            ? $this->response = ['message' => $this->message]
            : null;
    }

    // MARK: CHECKERS

    protected function checkValidationErrors(): void
    {
        if (!empty($this->errors->getValidationErrors())) {
            $this->status = 400;
            $this->message = "Hay errores de validación";
            $this->sendResponse();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (!empty($this->errors->getIntegrityErrors())) {
            $this->message = "Hay errores de integridad";
            $this->sendResponse();
        }
    }
}
