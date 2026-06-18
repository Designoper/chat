<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Mensaje.php';
require_once __DIR__ . '/models/Grupo.php';
require_once __DIR__ . '/models/Conexion.php';
require_once __DIR__ . '/models/Contacto.php';

final readonly class Router
{
    private const COMMON_PATH = '/api/';
    private array $routes;

    public function __construct()
    {
        $GET = 'GET';
        $POST = 'POST';

        $this->routes = [
            $this->makeRoute($GET, 'usuarios/current', [Usuario::class, 'currentUsuario']),
            $this->makeRoute($GET, 'mensajes/stream', [Mensaje::class, 'streamMensajes']),
            $this->makeRoute($GET, 'mensajes/ultimo-id', [Mensaje::class, 'getUltimoIdMensaje']),
            $this->makeRoute($GET, 'mensajes', [Mensaje::class, 'readMensajes']),

            $this->makeRoute($GET, 'grupos/no-miembro/stream', [Grupo::class, 'streamGruposNoMiembro']),
            $this->makeRoute($GET, 'grupos/stream', [Grupo::class, 'streamGrupos']),

            $this->makeRoute($GET, 'conexion/stream', [Conexion::class, 'streamConexion']),
            $this->makeRoute($GET, 'contactos/stream', [Contacto::class, 'streamContactos']),

            $this->makeRoute($POST, 'usuarios/crear', [Usuario::class, 'createUsuario']),
            $this->makeRoute($POST, 'usuarios/login', [Usuario::class, 'login']),
            $this->makeRoute($POST, 'usuarios/logout', [Usuario::class, 'logout']),
            $this->makeRoute($POST, 'usuarios/delete', [Usuario::class, 'deleteUsuario']),

            $this->makeRoute($POST, 'mensajes/crear', [Mensaje::class, 'createMensaje']),
            $this->makeRoute($POST, 'mensajes/delete', [Mensaje::class, 'deleteMensaje']),
            $this->makeRoute($POST, 'mensajes/ultimo-id', [Mensaje::class, 'setUltimoIdLeido']),

            $this->makeRoute($POST, 'grupos/crear', [Grupo::class, 'createGrupo']),
            $this->makeRoute($POST, 'grupos/invitar', [Grupo::class, 'invitar']),
            $this->makeRoute($POST, 'grupos/aceptar', [Grupo::class, 'aceptarInvitacion']),
            $this->makeRoute($POST, 'grupos/rechazar', [Grupo::class, 'rechazarInvitacion']),
            $this->makeRoute($POST, 'grupos/abandonar', [Grupo::class, 'abandonarGrupo']),
            $this->makeRoute($POST, 'grupos/delete', [Grupo::class, 'deleteGrupo']),

            $this->makeRoute($POST, 'conexion/estado', [Conexion::class, 'setConexion']),
        ];

        $this->handleRequest();
    }

    private function makeRoute(string $method, string $path, array $action): array
    {
        [$class, $methodName] = $action;

        return [
            'method'  => $method,
            'path'    => self::COMMON_PATH . $path,
            'handler' => fn() => (new $class())->$methodName()
        ];
    }

    private function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match("#^{$route['path']}#", $requestUri)) {
                $route['handler']();
                return;
            }
        }

        http_response_code(404);
        echo json_encode("La ruta solicitada no existe: $requestUri");
    }
}

new Router();
