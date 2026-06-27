<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Mensaje.php';
require_once __DIR__ . '/models/Grupo.php';
require_once __DIR__ . '/models/Conexion.php';
require_once __DIR__ . '/models/Contacto.php';

enum HTTPMethods: string
{
    case GET = 'GET';
    case POST = 'POST';
}

final readonly class Router
{
    private const COMMON_PATH = '/api/';
    private array $routes;

    public function __construct()
    {
        $this->routes = [
            $this->makeRoute(HTTPMethods::GET, 'usuarios/current', [Usuario::class, 'currentUsuario']),

            $this->makeRoute(HTTPMethods::GET, 'mensajes/stream', [Mensaje::class, 'streamMensajes']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes/ultimo-id', [Mensaje::class, 'getUltimoIdMensaje']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes', [Mensaje::class, 'readMensajes']),

            $this->makeRoute(HTTPMethods::GET, 'grupos/no-miembro/stream', [Grupo::class, 'streamGruposNoMiembro']),
            $this->makeRoute(HTTPMethods::GET, 'grupos/stream', [Grupo::class, 'streamGrupos']),

            $this->makeRoute(HTTPMethods::GET, 'conexion/stream', [Conexion::class, 'streamConexion']),

            $this->makeRoute(HTTPMethods::GET, 'contactos/stream', [Contacto::class, 'streamContactos']),

            $this->makeRoute(HTTPMethods::POST, 'usuarios/crear', [Usuario::class, 'createUsuario']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/login', [Usuario::class, 'login']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/logout', [Usuario::class, 'logout']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/delete', [Usuario::class, 'deleteUsuario']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/nombre', [Usuario::class, 'cambiarNombre']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/password', [Usuario::class, 'cambiarPassword']),

            $this->makeRoute(HTTPMethods::POST, 'mensajes/crear', [Mensaje::class, 'createMensaje']),
            $this->makeRoute(HTTPMethods::POST, 'mensajes/delete', [Mensaje::class, 'deleteMensaje']),
            $this->makeRoute(HTTPMethods::POST, 'mensajes/ultimo-id', [Mensaje::class, 'setUltimoIdLeido']),

            $this->makeRoute(HTTPMethods::POST, 'grupos/crear', [Grupo::class, 'createGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/invitar', [Grupo::class, 'invitar']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/aceptar', [Grupo::class, 'aceptarInvitacion']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/rechazar', [Grupo::class, 'rechazarInvitacion']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/abandonar', [Grupo::class, 'abandonarGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/delete', [Grupo::class, 'deleteGrupo']),

            $this->makeRoute(HTTPMethods::POST, 'conexion/estado', [Conexion::class, 'setConexion']),
        ];

        $this->handleRequest();
    }

    private function makeRoute(HTTPMethods $method, string $path, array $action): array
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
        $method = HTTPMethods::from($_SERVER['REQUEST_METHOD']);
        $requestUri = $_SERVER['REQUEST_URI'];

        switch ($method) {
            case HTTPMethods::GET:
            case HTTPMethods::POST:

                foreach ($this->routes as $route) {
                    if ($route['method'] === $method && preg_match("#^{$route['path']}#", $requestUri)) {
                        $route['handler']();
                        return;
                    }
                }

                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode("La ruta solicitada no existe: $requestUri", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
}

new Router();
