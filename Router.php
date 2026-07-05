<?php

declare(strict_types=1);

// require_once __DIR__ . '/models/Conexion.php';
require_once __DIR__ . '/models/Mensaje.php';

final readonly class Router
{
    private const COMMON_PATH = '/api/';
    private array $routes;

    public function __construct()
    {
        $this->routes = [
            $this->setRoute('GET', 'usuarios/current', [Usuario::class, 'currentUsuario']),

            $this->setRoute('GET', 'invitaciones/contactos-invitables', [Invitacion::class, 'streamContactosInvitables']),
            $this->setRoute('GET', 'invitaciones/stream', [Invitacion::class, 'streamInvitaciones']),

            $this->setRoute('GET', 'contactos/stream', [Contacto::class, 'streamContactos']),

            // $this->setRoute('GET', 'conexion/stream', [Conexion::class, 'streamConexion']),

            $this->setRoute('GET', 'mensajes/stream/directos', [Mensaje::class, 'streamMensajesDirectos']),
            $this->setRoute('GET', 'mensajes/stream/grupales', [Mensaje::class, 'streamMensajesGrupales']),
            $this->setRoute('GET', 'mensajes/directos', [Mensaje::class, 'readMensajesDirectos']),
            $this->setRoute('GET', 'mensajes/grupales', [Mensaje::class, 'readMensajesGrupales']),
            $this->setRoute('GET', 'mensajes/ultimo-ulid-directo', [Mensaje::class, 'getUltimoUlidDirecto']),
            $this->setRoute('GET', 'mensajes/ultimo-ulid-grupal', [Mensaje::class, 'getUltimoUlidGrupal']),

            $this->setRoute('GET', 'mensajes/archivos/directo', [Mensaje::class, 'readArchivoMensajeDirecto']),
            $this->setRoute('GET', 'mensajes/archivos/grupal', [Mensaje::class, 'readArchivoMensajeGrupal']),

            $this->setRoute('POST', 'usuarios/crear', [Usuario::class, 'createUsuario']),
            $this->setRoute('POST', 'usuarios/login', [Usuario::class, 'login']),
            $this->setRoute('POST', 'usuarios/logout', [Usuario::class, 'logout']),
            $this->setRoute('POST', 'usuarios/delete', [Usuario::class, 'deleteUsuario']),
            $this->setRoute('POST', 'usuarios/nombre', [Usuario::class, 'cambiarNombre']),
            $this->setRoute('POST', 'usuarios/password', [Usuario::class, 'cambiarPassword']),

            $this->setRoute('POST', 'grupos/crear', [Grupo::class, 'createGrupo']),
            $this->setRoute('POST', 'grupos/delete', [Grupo::class, 'deleteGrupo']),
            $this->setRoute('POST', 'grupos/abandonar', [Grupo::class, 'abandonarGrupo']),

            $this->setRoute('POST', 'invitaciones/usuarios/invitar', [Invitacion::class, 'invitarContacto']),
            $this->setRoute('POST', 'invitaciones/usuarios/aceptar', [Invitacion::class, 'aceptarContacto']),
            $this->setRoute('POST', 'invitaciones/usuarios/rechazar', [Invitacion::class, 'rechazarContacto']),
            $this->setRoute('POST', 'invitaciones/grupos/invitar', [Invitacion::class, 'invitarGrupo']),
            $this->setRoute('POST', 'invitaciones/grupos/aceptar', [Invitacion::class, 'aceptarGrupo']),
            $this->setRoute('POST', 'invitaciones/grupos/rechazar', [Invitacion::class, 'rechazarGrupo']),

            // $this->setRoute('POST', 'conexion/estado', [Conexion::class, 'setConexion']),

            $this->setRoute('POST', 'mensajes/crear/directo', [Mensaje::class, 'createMensajeDirecto']),
            $this->setRoute('POST', 'mensajes/crear/imagen-directo', [Mensaje::class, 'createMensajeDirectoImagen']),
            $this->setRoute('POST', 'mensajes/crear/audio-directo', [Mensaje::class, 'createMensajeDirectoAudio']),
            $this->setRoute('POST', 'mensajes/crear/video-directo', [Mensaje::class, 'createMensajeDirectoVideo']),
            $this->setRoute('POST', 'mensajes/crear/grupal', [Mensaje::class, 'createMensajeGrupal']),
            $this->setRoute('POST', 'mensajes/crear/imagen-grupal', [Mensaje::class, 'createMensajeGrupalImagen']),
            $this->setRoute('POST', 'mensajes/crear/audio-grupal', [Mensaje::class, 'createMensajeGrupalAudio']),
            $this->setRoute('POST', 'mensajes/crear/video-grupal', [Mensaje::class, 'createMensajeGrupalVideo']),
            $this->setRoute('POST', 'mensajes/delete', [Mensaje::class, 'deleteMensaje']),
            $this->setRoute('POST', 'mensajes/ultimo-ulid-directo', [Mensaje::class, 'setUltimoUlidDirecto']),
            $this->setRoute('POST', 'mensajes/ultimo-ulid-grupal', [Mensaje::class, 'setUltimoUlidGrupal']),
        ];

        $this->executeRoute();
    }

    // ============================================================================
    // MARK: SET ROUTE
    // ============================================================================
    private function setRoute(string $method, string $path, array $action): array
    {
        [$class, $methodName] = $action;

        return [
            'method'  => $method,
            'path'    => self::COMMON_PATH . $path,
            'handler' => fn() => (new $class())->$methodName()
        ];
    }

    // ============================================================================
    // MARK: EXECUTE ROUTE
    // ============================================================================
    private function executeRoute(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        $domain = $protocol . '://' . $host;
        $completeUrl = $domain . $requestUri;

        $method = $_SERVER['REQUEST_METHOD'];

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
                echo json_encode(
                    "La ruta $method solicitada no existe: $completeUrl",
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                return;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                header("Content-Type: application/json");
                echo json_encode("Solo se permiten solicitudes GET y POST.", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}

new Router();
