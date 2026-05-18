<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Mensaje.php';
require_once __DIR__ . '/../../models/Grupo.php';
require_once __DIR__ . '/../../models/Conexion.php';

final class Router
{
    private const string COMMON_PATH = '/api/';
    private array $routes;

    public function __construct()
    {

        // MARK: GET ROUTES

        $this->setRoute('GET', 'stream-mensajes', Mensaje::class, 'streamMensajes');
        $this->setRoute('GET', 'mensajes/no-leidos', Mensaje::class, 'countUnreadMessages');
        $this->setRoute('GET', 'mensajes', Mensaje::class, 'readMensajes');
        $this->setRoute('GET', 'usuarios/current', Usuario::class, 'currentUsuario');
        $this->setRoute('GET', 'usuarios$', Usuario::class, 'readUsuarios');
        $this->setRoute('GET', 'grupos$', Grupo::class, 'readGrupos');
        $this->setRoute('GET', 'grupos/miembro$', Grupo::class, 'readGruposMiembro');
        $this->setRoute('GET', 'grupos/pendiente$', Grupo::class, 'readGruposPendiente');
        $this->setRoute('GET', 'grupos/no-miembro', Grupo::class, 'readGruposNoMiembro');
        $this->setRoute('GET', 'stream-conexion', Conexion::class, 'streamConexion');

        // MARK: POST ROUTES

        $this->setRoute('POST', 'usuarios/crear', Usuario::class, 'createUsuario');
        $this->setRoute('POST', 'usuarios/login', Usuario::class, 'login');
        $this->setRoute('POST', 'usuarios/logout', Usuario::class, 'logout');
        $this->setRoute('POST', 'usuarios/delete', Usuario::class, 'deleteUsuario');
        $this->setRoute('POST', 'mensajes/ultimo-id', Mensaje::class, 'setUltimoIdMensaje');
        $this->setRoute('POST', 'mensajes/crear', Mensaje::class, 'createMensaje');
        $this->setRoute('POST', 'mensajes-directos/crear', Mensaje::class, 'createMensajeDirecto');
        $this->setRoute('POST', 'mensajes-grupales/crear', Mensaje::class, 'createMensajeGrupal');
        $this->setRoute('POST', 'mensajes/[1-9]\d*$', Mensaje::class, 'deleteMensaje');
        $this->setRoute('POST', 'grupos/crear', Grupo::class, 'createGrupo');
        $this->setRoute('POST', 'grupos/invitar', Grupo::class, 'invitar');
        $this->setRoute('POST', 'grupos/aceptar', Grupo::class, 'aceptarInvitacion');
        $this->setRoute('POST', 'conexion/estado', Conexion::class, 'setConexion');

        $this->handleRequest();
    }

    // MARK: SET ROUTE

    private function setRoute(string $method, string $path, string $clase, string $metodo): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => self::COMMON_PATH . $path,
            'handler' => function () use ($clase, $metodo): void {
                (new $clase())->$metodo();
            }
        ];
    }

    // MARK: HANDLE REQUEST

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
