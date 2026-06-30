<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Mensaje.php';
require_once __DIR__ . '/models/Grupo.php';
require_once __DIR__ . '/models/Conexion.php';
require_once __DIR__ . '/models/Contacto.php';
require_once __DIR__ . '/models/Invitacion.php';

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

            $this->makeRoute(HTTPMethods::GET, 'invitaciones/contactos-invitables', [Invitacion::class, 'streamContactosInvitables']),
            $this->makeRoute(HTTPMethods::GET, 'invitaciones/contactos', [Invitacion::class, 'streamInvitacionesContacto']),
            $this->makeRoute(HTTPMethods::GET, 'invitaciones/grupos', [Invitacion::class, 'streamInvitacionesGrupo']),
            $this->makeRoute(HTTPMethods::GET, 'invitaciones/stream', [Invitacion::class, 'streamInvitaciones']),

            $this->makeRoute(HTTPMethods::GET, 'contactos/stream', [Contacto::class, 'streamContactos']),

            $this->makeRoute(HTTPMethods::GET, 'conexion/stream', [Conexion::class, 'streamConexion']),

            $this->makeRoute(HTTPMethods::GET, 'mensajes/stream/directos', [Mensaje::class, 'streamMensajesDirectos']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes/stream/grupales', [Mensaje::class, 'streamMensajesGrupales']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes/directos', [Mensaje::class, 'readMensajesDirectos']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes/grupales', [Mensaje::class, 'readMensajesGrupales']),
            $this->makeRoute(HTTPMethods::GET, 'mensajes/ultimo-id', [Mensaje::class, 'getUltimoIdMensaje']),

            $this->makeRoute(HTTPMethods::POST, 'usuarios/crear', [Usuario::class, 'createUsuario']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/login', [Usuario::class, 'login']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/logout', [Usuario::class, 'logout']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/delete', [Usuario::class, 'deleteUsuario']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/nombre', [Usuario::class, 'cambiarNombre']),
            $this->makeRoute(HTTPMethods::POST, 'usuarios/password', [Usuario::class, 'cambiarPassword']),

            $this->makeRoute(HTTPMethods::POST, 'grupos/crear', [Grupo::class, 'createGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/delete', [Grupo::class, 'deleteGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'grupos/abandonar', [Grupo::class, 'abandonarGrupo']),

            $this->makeRoute(HTTPMethods::POST, 'invitaciones/usuarios/invitar', [Invitacion::class, 'invitarContacto']),
            $this->makeRoute(HTTPMethods::POST, 'invitaciones/usuarios/aceptar', [Invitacion::class, 'aceptarContacto']),
            $this->makeRoute(HTTPMethods::POST, 'invitaciones/usuarios/rechazar', [Invitacion::class, 'rechazarContacto']),
            $this->makeRoute(HTTPMethods::POST, 'invitaciones/grupos/invitar', [Invitacion::class, 'invitarGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'invitaciones/grupos/aceptar', [Invitacion::class, 'aceptarGrupo']),
            $this->makeRoute(HTTPMethods::POST, 'invitaciones/grupos/rechazar', [Invitacion::class, 'rechazarGrupo']),

            $this->makeRoute(HTTPMethods::POST, 'conexion/estado', [Conexion::class, 'setConexion']),

            $this->makeRoute(HTTPMethods::POST, 'mensajes/crear/directo', [Mensaje::class, 'createMensajeDirecto']),
            $this->makeRoute(HTTPMethods::POST, 'mensajes/crear/grupal', [Mensaje::class, 'createMensajeGrupal']),
            $this->makeRoute(HTTPMethods::POST, 'mensajes/delete', [Mensaje::class, 'deleteMensaje']),
            $this->makeRoute(HTTPMethods::POST, 'mensajes/ultimo-id', [Mensaje::class, 'setUltimoIdLeido']),

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
