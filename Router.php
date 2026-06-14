<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Mensaje.php';
require_once __DIR__ . '/models/Grupo.php';
require_once __DIR__ . '/models/Conexion.php';
require_once __DIR__ . '/models/Contacto.php';

final class Router
{
    private const string COMMON_PATH = '/api/';
    private array $routes = [];

    public function __construct()
    {
        $GET = 'GET';
        $POST = 'POST';

        // Usuarios
        $this->setRoute($GET, 'usuarios/current', [Usuario::class, 'currentUsuario']);

        // Mensajes
        $this->setRoute($GET, 'mensajes/stream', [Mensaje::class, 'streamMensajes']);
        $this->setRoute($GET, 'mensajes/ultimo-id', [Mensaje::class, 'getUltimoIdMensaje']);
        $this->setRoute($GET, 'mensajes', [Mensaje::class, 'readMensajes']);

        // Grupos
        $this->setRoute($GET, 'grupos/miembro$', [Grupo::class, 'readGruposMiembro']);
        $this->setRoute($GET, 'grupos/pendiente$', [Grupo::class, 'readGruposPendiente']);
        $this->setRoute($GET, 'grupos/no-miembro/stream', [Grupo::class, 'streamGruposNoMiembro']);
        $this->setRoute($GET, 'grupos/no-miembro', [Grupo::class, 'readGruposNoMiembro']);
        $this->setRoute($GET, 'grupos/stream', [Grupo::class, 'streamGrupos']);

        // Conexión
        $this->setRoute($GET, 'conexion/stream', [Conexion::class, 'streamConexion']);

        // Contactos
        $this->setRoute($GET, 'contactos/stream', [Contacto::class, 'streamContactos']);

        // Usuarios
        $this->setRoute($POST, 'usuarios/crear', [Usuario::class, 'createUsuario']);
        $this->setRoute($POST, 'usuarios/login', [Usuario::class, 'login']);
        $this->setRoute($POST, 'usuarios/logout', [Usuario::class, 'logout']);
        $this->setRoute($POST, 'usuarios/delete', [Usuario::class, 'deleteUsuario']);

        // Mensajes
        $this->setRoute($POST, 'mensajes/crear', [Mensaje::class, 'createMensaje']);
        $this->setRoute($POST, 'mensajes/delete', [Mensaje::class, 'deleteMensaje']);
        $this->setRoute($POST, 'mensajes/ultimo-id', [Mensaje::class, 'setUltimoIdLeido']);

        // Grupos
        $this->setRoute($POST, 'grupos/crear', [Grupo::class, 'createGrupo']);
        $this->setRoute($POST, 'grupos/invitar', [Grupo::class, 'invitar']);
        $this->setRoute($POST, 'grupos/aceptar', [Grupo::class, 'aceptarInvitacion']);
        $this->setRoute($POST, 'grupos/rechazar', [Grupo::class, 'rechazarInvitacion']);
        $this->setRoute($POST, 'grupos/abandonar', [Grupo::class, 'abandonarGrupo']);
        $this->setRoute($POST, 'grupos/delete', [Grupo::class, 'deleteGrupo']);

        // Conexión
        $this->setRoute($POST, 'conexion/estado', [Conexion::class, 'setConexion']);

        $this->handleRequest();
    }

    // MARK: SET ROUTE
    private function setRoute(string $method, string $path, array $action): void
    {
        [$class, $methodName] = $action;

        $this->routes[] = [
            'method'  => $method,
            'path'    => self::COMMON_PATH . $path,
            'handler' => fn() => (new $class())->$methodName()
        ];
    }

    // MARK: HANDLE REQUEST
    private function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        switch ($method) {
            case 'GET':
            case 'POST':

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
