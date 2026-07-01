-- DROP DATABASE IF EXISTS chat;
-- CREATE DATABASE chat CHARACTER SET utf8mb4;
-- USE chat;

SET default_storage_engine=InnoDB;

-- MARK: USUARIOS

CREATE TABLE usuarios (
    ulid_usuario CHAR(26) PRIMARY KEY,
    nombre_usuario VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    codigo_contacto CHAR(6) NOT NULL UNIQUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- MARK: GRUPOS

CREATE TABLE grupos (
    ulid_grupo CHAR(26) PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL UNIQUE,
    ulid_fundador CHAR(26) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,

    FOREIGN KEY (ulid_fundador)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE SET NULL
);

-- MARK: INVITACIONES DIRECTAS

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

-- MARK: INVITACIONES GRUPALES

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

-- MARK: CONTACTOS DIRECTOS

CREATE TABLE contactos_directos (
    ulid_a CHAR(26) NOT NULL,
    ulid_b CHAR(26) NOT NULL,

    CHECK (ulid_a < ulid_b),

    UNIQUE (ulid_a, ulid_b),

    FOREIGN KEY (ulid_a) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE,
    FOREIGN KEY (ulid_b) REFERENCES usuarios(ulid_usuario) ON DELETE CASCADE
);

-- MARK: CONTACTOS GRUPALES

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

-- MARK: MENSAJES

CREATE TABLE mensajes (
    ulid_mensaje CHAR(26) PRIMARY KEY,
    contenido TEXT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    imagen VARCHAR(255) NULL,
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

-- MARK: ULTIMOS MENSAJES LEIDOS DIRECTOS

CREATE TABLE ultimos_mensajes_leidos_directos (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_contacto CHAR(26) NOT NULL,
    ulid_mensaje CHAR(26) NOT NULL,

    UNIQUE KEY unico_privado (ulid_usuario, ulid_contacto),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_contacto)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE
);

-- MARK: ULTIMOS MENSAJES LEIDOS GRUPALES

CREATE TABLE ultimos_mensajes_leidos_grupales (
    ulid_usuario CHAR(26) NOT NULL,
    ulid_grupo CHAR(26) NOT NULL,
    ulid_mensaje CHAR(26) NOT NULL,

    UNIQUE KEY unico_grupo (ulid_usuario, ulid_grupo),

    FOREIGN KEY (ulid_usuario)
        REFERENCES usuarios(ulid_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (ulid_grupo)
        REFERENCES grupos(ulid_grupo)
        ON DELETE CASCADE
);

-- CREATE TABLE conexion_directa (
--     ulid_usuario INT UNSIGNED NOT NULL,
--     ulid_contacto INT UNSIGNED NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_directo (ulid_usuario, ulid_contacto),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_contacto)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE
-- );

-- CREATE TABLE conexion_grupal (
--     ulid_usuario INT UNSIGNED NOT NULL,
--     ulid_grupo INT UNSIGNED NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_grupal (ulid_usuario, ulid_grupo),

--     FOREIGN KEY (ulid_usuario)
--         REFERENCES usuarios(ulid_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (ulid_grupo)
--         REFERENCES grupos(ulid_grupo)
--         ON DELETE CASCADE
-- );
