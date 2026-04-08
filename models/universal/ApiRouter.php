<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/../../models/usuario/Usuario.php';
require_once __DIR__ . '/../../models/mensaje/Mensaje.php';

final class ApiRouter extends Sanitizer
{
    private const string COMMON_PATH = '/api/';
    private array $routes = [];

    public function __construct()
    {
        parent::__construct();

        // MARK: GET ROUTES

        $this->setRoute(
            'GET',
            'mensajes$',
            function (): void {
                $mensaje = new Mensaje();
                $mensaje->readMensajes();
            }
        );

        // MARK: POST ROUTES

        $this->setRoute(
            'POST',
            'usuarios',
            function (): void {
                $usuario = new Usuario();
                $usuario->createUsuario();
            }
        );

        $this->setRoute(
            'POST',
            'login',
            function (): void {
                $usuario = new Usuario();
                $usuario->login();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes$',
            function (): void {
                $mensaje = new Mensaje();
                $mensaje->createMensaje();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes/[1-9]\d*$',
            function (): void {
                $mensaje = new Mensaje();
                $mensaje->deleteMensaje();
            }
        );

        // UPDATE ROUTES

        $this->handleRequest();
    }

    private function setRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => self::COMMON_PATH . $path,
            'handler' => $handler
        ];
    }

    private function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $requestUri = $_SERVER['REQUEST_URI']; // Gets full URI including query string

                foreach ($this->routes as $route) {
                    if ($route['method'] === $method && preg_match("#^{$route['path']}#", $requestUri)) {
                        $route['handler']();
                    }
                }

                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'message' => 'La ruta solicitada no existe',
                    'requested_path' => $requestUri
                ]);
                break;

            default:
                http_response_code(405);
                header('Allow: GET, POST, PUT, DELETE');
        }
    }
}
