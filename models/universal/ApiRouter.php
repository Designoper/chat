<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/../../models/usuario/Usuario.php';
require_once __DIR__ . '/../../models/mensaje/Mensaje.php';
// require_once __DIR__ . '/../../models/mensaje/MensajeDirecto.php';

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
            'mensajes-directos',
            function (): void {
                new Mensaje()->readMensajesDirectos();
            }
        );

        $this->setRoute(
            'GET',
            'mensajes$',
            function (): void {
                new Mensaje()->readMensajes();
            }
        );

        $this->setRoute(
            'GET',
            'usuarios/current',
            function (): void {
                new Usuario()->currentUsuario();
            }
        );

        $this->setRoute(
            'GET',
            'usuarios$',
            function (): void {
                new Usuario()->readUsuarios();
            }
        );

        // MARK: POST ROUTES

        $this->setRoute(
            'POST',
            'usuarios/receptor',
            function (): void {
                new Usuario()->usuarioReceptor();
            }
        );

        $this->setRoute(
            'POST',
            'usuarios/crear',
            function (): void {
                new Usuario()->createUsuario();
            }
        );

        $this->setRoute(
            'POST',
            'usuarios/login',
            function (): void {
                new Usuario()->login();
            }
        );

        $this->setRoute(
            'POST',
            'usuarios/logout',
            function (): void {
                new Usuario()->logout();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes/crear',
            function (): void {
                new Mensaje()->createMensaje();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes-directos/crear',
            function (): void {
                new Mensaje()->createMensajeDirecto();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes/[1-9]\d*$',
            function (): void {
                new Mensaje()->deleteMensaje();
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
