<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Mensaje.php';
require_once __DIR__ . '/../../models/Grupo.php';

final class Router
{
    private const string COMMON_PATH = '/api/';
    private array $routes = [];

    public function __construct()
    {

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
            'mensajes-grupales',
            function (): void {
                new Mensaje()->readMensajesGrupales();
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

        $this->setRoute(
            'GET',
            'grupos$',
            function (): void {
                new Grupo()->readGrupos();
            }
        );

        $this->setRoute(
            'GET',
            'grupos/miembro$',
            function (): void {
                new Grupo()->readGruposMiembro();
            }
        );

        $this->setRoute(
            'GET',
            'grupos/pendiente$',
            function (): void {
                new Grupo()->readGruposPendiente();
            }
        );

        $this->setRoute(
            'GET',
            'grupos/no-miembro',
            function (): void {
                new Grupo()->readGruposNoMiembro();
            }
        );

        // MARK: POST ROUTES

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
            'mensajes-grupales/crear',
            function (): void {
                new Mensaje()->createMensajeGrupal();
            }
        );

        $this->setRoute(
            'POST',
            'mensajes/[1-9]\d*$',
            function (): void {
                new Mensaje()->deleteMensaje();
            }
        );

        $this->setRoute(
            'POST',
            'grupos/crear',
            function (): void {
                new Grupo()->createGrupo();
            }
        );

        $this->setRoute(
            'POST',
            'grupos/invitar',
            function (): void {
                new Grupo()->invitar();
            }
        );

        $this->setRoute(
            'POST',
            'grupos/aceptar',
            function (): void {
                new Grupo()->aceptarInvitacion();
            }
        );

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
