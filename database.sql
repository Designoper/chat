DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

SET default_storage_engine=InnoDB;

-- ============================================================================
-- MARK: USUARIOS
-- ============================================================================
CREATE TABLE usuarios (
    ulid_usuario CHAR(26) PRIMARY KEY,
    nombre_usuario VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    codigo_contacto CHAR(6) NOT NULL UNIQUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- ============================================================================
-- MARK: GRUPOS
-- ============================================================================
CREATE TABLE grupos (
    ulid_grupo CHAR(26) PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL UNIQUE,
    ulid_fundador CHAR(26) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,

    FOREIGN KEY (ulid_fundador)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE SET NULL
);

-- ============================================================================
-- MARK: INVITACIONES DIRECTAS
-- ============================================================================
CREATE TABLE invitaciones_directas (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_contacto CHAR(26) NOT NULL,

    UNIQUE KEY invitacion_directa (ulid_usuario, ulid_contacto),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_contacto)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE
);

-- ============================================================================
-- MARK: INVITACIONES GRUPALES
-- ============================================================================
CREATE TABLE invitaciones_grupales (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_grupo CHAR(26) NOT NULL,

    UNIQUE KEY invitacion_grupo (ulid_usuario, ulid_grupo),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_grupo)
        REFERENCES grupos(ulid_grupo)
        ON DELETE CASCADE
);

-- ============================================================================
-- MARK: CONTACTOS DIRECTOS
-- ============================================================================
CREATE TABLE contactos_directos (
    ulid_min CHAR(26) NOT NULL,
    ulid_max CHAR(26) NOT NULL,

    CHECK (ulid_min < ulid_max),

    UNIQUE KEY contacto_directo (ulid_min, ulid_max),

    FOREIGN KEY (ulid_min) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE,
    FOREIGN KEY (ulid_max) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE
);

-- ============================================================================
-- MARK: CONTACTOS GRUPALES
-- ============================================================================
CREATE TABLE contactos_grupales (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_grupo CHAR(26) NOT NULL,

    UNIQUE KEY contacto_grupal (ulid_usuario, ulid_grupo),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_grupo)
        REFERENCES grupos(ulid_grupo)
        ON DELETE CASCADE
);

-- ============================================================================
-- MARK: MENSAJES
-- ============================================================================
CREATE TABLE mensajes (
    ulid_mensaje CHAR(26) PRIMARY KEY,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    tipo_mensaje ENUM('text', 'image', 'audio', 'video') NOT NULL,
    ulid_emisor CHAR(26) NULL,
    ulid_contacto CHAR(26) NULL,
    ulid_grupo CHAR(26) NULL,

    FOREIGN KEY (ulid_emisor)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE SET NULL,

    FOREIGN KEY (ulid_contacto)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE SET NULL,

    FOREIGN KEY (ulid_grupo)
        REFERENCES grupos(ulid_grupo)
        ON DELETE SET NULL
);

-- ============================================================================
-- MARK: ULTIMOS MENSAJES LEIDOS DIRECTOS
-- ============================================================================
CREATE TABLE ultimos_mensajes_leidos_directos (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_contacto CHAR(26) NOT NULL,
    ulid_mensaje CHAR(26) NOT NULL,

    UNIQUE KEY ultimo_mensaje_leido_directo (ulid_usuario, ulid_contacto),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_contacto)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE
);

-- ============================================================================
-- MARK: ULTIMOS MENSAJES LEIDOS GRUPALES
-- ============================================================================
CREATE TABLE ultimos_mensajes_leidos_grupales (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_grupo CHAR(26) NOT NULL,
    ulid_mensaje CHAR(26) NOT NULL,

    UNIQUE KEY ultimo_mensaje_leido_grupal (ulid_usuario, ulid_grupo),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_grupo)
        REFERENCES grupos(ulid_grupo)
        ON DELETE CASCADE
);

-- ============================================================================
-- MARK: CONEXION DIRECTA
-- ============================================================================
-- CREATE TABLE conexion_directa (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_contacto CHAR(26) NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_directo (ulid_usuario, ulid_contacto),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_contacto)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE
-- );

-- ============================================================================
-- MARK: CONEXION GRUPAL
-- ============================================================================
-- CREATE TABLE conexion_grupal (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_grupo CHAR(26) NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_grupal (ulid_usuario, ulid_grupo),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE CASCADE
-- );






-- DROP DATABASE IF EXISTS chat;
-- CREATE DATABASE chat CHARACTER SET utf8mb4;
-- USE chat;

-- SET default_storage_engine=InnoDB;

-- -- ============================================================================
-- -- MARK: USUARIOS
-- -- ============================================================================
-- CREATE TABLE usuarios (
--     ulid_usuario CHAR(26) PRIMARY KEY,
--     nombre_usuario VARCHAR(20) NOT NULL UNIQUE,
--     password VARCHAR(255) NOT NULL,
--     codigo_contacto CHAR(6) NOT NULL UNIQUE,
--     fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,

--     -- Restricciones de longitud y formato
--     CONSTRAINT chk_usuarios_ulid_len CHECK (CHAR_LENGTH(ulid_usuario) = 26),
--     CONSTRAINT chk_usuarios_nombre_valido CHECK (TRIM(nombre_usuario) != '' AND CHAR_LENGTH(nombre_usuario) <= 20),
--     CONSTRAINT chk_usuarios_codigo_len CHECK (CHAR_LENGTH(codigo_contacto) = 6)
-- );

-- -- ============================================================================
-- -- MARK: GRUPOS
-- -- ============================================================================
-- CREATE TABLE grupos (
--     ulid_grupo CHAR(26) PRIMARY KEY,
--     nombre_grupo VARCHAR(20) NOT NULL UNIQUE,
--     ulid_fundador CHAR(26) NULL,
--     fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,

--     FOREIGN KEY (ulid_fundador)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE SET NULL,

--     -- Restricciones de integridad
--     CONSTRAINT chk_grupos_ulid_len CHECK (CHAR_LENGTH(ulid_grupo) = 26),
--     CONSTRAINT chk_grupos_nombre_valido CHECK (TRIM(nombre_grupo) != '' AND CHAR_LENGTH(nombre_grupo) <= 20),
--     CONSTRAINT chk_grupos_fundador_len CHECK (ulid_fundador IS NULL OR CHAR_LENGTH(ulid_fundador) = 26)
-- );

-- -- ============================================================================
-- -- MARK: INVITACIONES DIRECTAS
-- -- ============================================================================
-- CREATE TABLE invitaciones_directas (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_contacto CHAR(26) NOT NULL,

--     UNIQUE KEY invitacion_directa (ulid_usuario, ulid_contacto),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_contacto)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     -- Bloquea que un usuario se auto-invite
--     CONSTRAINT chk_inv_dir_len CHECK (CHAR_LENGTH(ulid_usuario) = 26 AND CHAR_LENGTH(ulid_contacto) = 26),
--     CONSTRAINT chk_inv_dir_no_autoinvitacion CHECK (ulid_usuario != ulid_contacto)
-- );

-- -- ============================================================================
-- -- MARK: INVITACIONES GRUPALES
-- -- ============================================================================
-- CREATE TABLE invitaciones_grupales (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_grupo CHAR(26) NOT NULL,

--     UNIQUE KEY invitacion_grupo (ulid_usuario, ulid_grupo),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE CASCADE,

--     CONSTRAINT chk_inv_grup_len CHECK (CHAR_LENGTH(ulid_usuario) = 26 AND CHAR_LENGTH(ulid_grupo) = 26)
-- );

-- -- ============================================================================
-- -- MARK: CONTACTOS DIRECTOS
-- -- ============================================================================
-- CREATE TABLE contactos_directos (
--     ulid_min CHAR(26) NOT NULL,
--     ulid_max CHAR(26) NOT NULL,

--     UNIQUE KEY contacto_directo (ulid_min, ulid_max),

--     FOREIGN KEY (ulid_min) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE,
--     FOREIGN KEY (ulid_max) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE,

--     -- Tu check original de orden combinado con validación de longitud
--     CONSTRAINT chk_cont_dir_orden CHECK (ulid_min < ulid_max),
--     CONSTRAINT chk_cont_dir_len CHECK (CHAR_LENGTH(ulid_min) = 26 AND CHAR_LENGTH(ulid_max) = 26)
-- );

-- -- ============================================================================
-- -- MARK: CONTACTOS GRUPALES
-- -- ============================================================================
-- CREATE TABLE contactos_grupales (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_grupo CHAR(26) NOT NULL,

--     UNIQUE KEY contacto_grupal (ulid_usuario, ulid_grupo),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE CASCADE,

--     CONSTRAINT chk_cont_grup_len CHECK (CHAR_LENGTH(ulid_usuario) = 26 AND CHAR_LENGTH(ulid_grupo) = 26)
-- );

-- -- ============================================================================
-- -- MARK: MENSAJES
-- -- ============================================================================
-- CREATE TABLE mensajes (
--     ulid_mensaje CHAR(26) PRIMARY KEY,
--     contenido TEXT NOT NULL,
--     fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
--     tipo_mensaje ENUM('text', 'image', 'audio', 'video') NOT NULL,
--     ulid_emisor CHAR(26) NULL,
--     ulid_contacto CHAR(26) NULL,
--     ulid_grupo CHAR(26) NULL,

--     FOREIGN KEY (ulid_emisor)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE SET NULL,

--     FOREIGN KEY (ulid_contacto)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE SET NULL,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE SET NULL,

--     -- Reglas críticas de negocio del Chat
--     CONSTRAINT chk_mensajes_ulid_len CHECK (CHAR_LENGTH(ulid_mensaje) = 26),
--     CONSTRAINT chk_mensajes_contenido_valido CHECK (TRIM(contenido) != ''),
--     CONSTRAINT chk_mensajes_no_automensaje CHECK (ulid_emisor != ulid_contacto),
--     -- Lógica XOR: El mensaje va obligatorio a un chat directo O a un grupo, pero jamás a ambos
--     CONSTRAINT chk_mensajes_destino_unico CHECK (
--         (ulid_contacto IS NOT NULL AND ulid_grupo IS NULL) OR
--         (ulid_contacto IS NULL AND ulid_grupo IS NOT NULL)
--     )
-- );

-- -- ============================================================================
-- -- MARK: ULTIMOS MENSAJES LEIDOS DIRECTOS
-- -- ============================================================================
-- CREATE TABLE ultimos_mensajes_leidos_directos (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_contacto CHAR(26) NOT NULL,
--     ulid_mensaje CHAR(26) NOT NULL,

--     UNIQUE KEY ultimo_mensaje_leido_directo (ulid_usuario, ulid_contacto),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_contacto)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     CONSTRAINT chk_leidos_dir_len CHECK (
--         CHAR_LENGTH(ulid_usuario) = 26 AND
--         CHAR_LENGTH(ulid_contacto) = 26 AND
--         CHAR_LENGTH(ulid_mensaje) = 26
--     )
-- );

-- -- ============================================================================
-- -- MARK: ULTIMOS MENSAJES LEIDOS GRUPALES
-- -- ============================================================================
-- CREATE TABLE ultimos_mensajes_leidos_grupales (
--     ulid_usuario CHAR(26) NOT NULL,
--     ulid_grupo CHAR(26) NOT NULL,
--     ulid_mensaje CHAR(26) NOT NULL,

--     UNIQUE KEY ultimo_mensaje_leido_grupal (ulid_usuario, ulid_grupo),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE CASCADE,

--     CONSTRAINT chk_leidos_grup_len CHECK (
--         CHAR_LENGTH(ulid_usuario) = 26 AND
--         CHAR_LENGTH(ulid_grupo) = 26 AND
--         CHAR_LENGTH(ulid_mensaje) = 26
--     )
-- );
