<?php

declare(strict_types=1);

// require_once __DIR__ . "/models/Conexion.php";
require_once __DIR__ . "/models/Mensaje.php";

final readonly class Router
{
    private const ROUTES = [
        "GET" => [
            "/api/usuarios/current" => [Usuario::class, "currentUsuario"],

            "/api/invitaciones/contactos-invitables" => [Invitacion::class, "streamContactosInvitables"],
            "/api/invitaciones/stream" => [Invitacion::class, "streamInvitaciones"],

            "/api/contactos/stream" => [Contacto::class, "streamContactos"],

            "/api/mensajes/stream/directos" => [Mensaje::class, "streamMensajesDirectos"],
            "/api/mensajes/stream/grupales" => [Mensaje::class, "streamMensajesGrupales"],
            "/api/mensajes/directos" => [Mensaje::class, "readMensajesDirectos"],
            "/api/mensajes/grupales" => [Mensaje::class, "readMensajesGrupales"],
            "/api/mensajes/ultimo-ulid-directo" => [Mensaje::class, "getUltimoUlidDirecto"],
            "/api/mensajes/ultimo-ulid-grupal" => [Mensaje::class, "getUltimoUlidGrupal"],

            "/api/mensajes/archivos/directo" => [Mensaje::class, "readArchivoMensajeDirecto"],
            "/api/mensajes/archivos/grupal" => [Mensaje::class, "readArchivoMensajeGrupal"],
        ],

        "POST" => [
            "/api/usuarios/crear" => [Usuario::class, "createUsuario"],
            "/api/usuarios/login" => [Usuario::class, "login"],
            "/api/usuarios/logout" => [Usuario::class, "logout"],
            "/api/usuarios/delete" => [Usuario::class, "deleteUsuario"],
            "/api/usuarios/nombre" => [Usuario::class, "cambiarNombre"],
            "/api/usuarios/password" => [Usuario::class, "cambiarPassword"],

            "/api/grupos/crear" => [Grupo::class, "createGrupo"],
            "/api/grupos/delete" => [Grupo::class, "deleteGrupo"],
            "/api/grupos/abandonar" => [Grupo::class, "abandonarGrupo"],

            "/api/invitaciones/usuarios/invitar" => [Invitacion::class, "invitarContacto"],
            "/api/invitaciones/usuarios/aceptar" => [Invitacion::class, "aceptarContacto"],
            "/api/invitaciones/usuarios/rechazar" => [Invitacion::class, "rechazarContacto"],
            "/api/invitaciones/grupos/invitar" => [Invitacion::class, "invitarGrupo"],
            "/api/invitaciones/grupos/aceptar" => [Invitacion::class, "aceptarGrupo"],
            "/api/invitaciones/grupos/rechazar" => [Invitacion::class, "rechazarGrupo"],

            "/api/mensajes/crear/directo" => [Mensaje::class, "createMensajeDirecto"],
            "/api/mensajes/crear/imagen-directo" => [Mensaje::class, "createMensajeDirectoImagen"],
            "/api/mensajes/crear/audio-directo" => [Mensaje::class, "createMensajeDirectoAudio"],
            "/api/mensajes/crear/video-directo" => [Mensaje::class, "createMensajeDirectoVideo"],
            "/api/mensajes/crear/ubicacion-directo" => [Mensaje::class, "createMensajeDirectoUbicacion"],
            "/api/mensajes/crear/grupal" => [Mensaje::class, "createMensajeGrupal"],
            "/api/mensajes/crear/imagen-grupal" => [Mensaje::class, "createMensajeGrupalImagen"],
            "/api/mensajes/crear/audio-grupal" => [Mensaje::class, "createMensajeGrupalAudio"],
            "/api/mensajes/crear/video-grupal" => [Mensaje::class, "createMensajeGrupalVideo"],
            "/api/mensajes/crear/ubicacion-grupal" => [Mensaje::class, "createMensajeGrupalUbicacion"],
            "/api/mensajes/delete" => [Mensaje::class, "deleteMensaje"],
            "/api/mensajes/ultimo-ulid-directo" => [Mensaje::class, "setUltimoUlidDirecto"],
            "/api/mensajes/ultimo-ulid-grupal" => [Mensaje::class, "setUltimoUlidGrupal"],
        ],
    ];

    public function __construct()
    {
        $this->executeRoute();
    }

    // ============================================================================
    // MARK: EXECUTE ROUTE
    // ============================================================================
    private function executeRoute(): void
    {
        // 1. Obtener solo el paso limpio de la URI (sin ?query_string)
        $requestUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        $protocol = $_SERVER["REQUEST_SCHEME"];
        $host = $_SERVER["HTTP_HOST"];
        $domain = $protocol . "://" . $host;
        $completeUrl = $domain . $_SERVER["REQUEST_URI"];

        $method = $_SERVER["REQUEST_METHOD"];

        switch ($method) {
            case "GET":
            case "POST":
                $handler = self::ROUTES[$method][$requestUri] ?? null;
                if ($handler !== null) {
                    [$class, $methodName] = $handler;
                    (new $class())->$methodName();
                    return;
                }

                http_response_code(404);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(
                    "La ruta $method solicitada no existe: $completeUrl",
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                );
                return;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode("Solo se permiten solicitudes GET y POST.", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    }
}

new Router();
