DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

SET default_storage_engine=InnoDB;

CREATE TABLE usuarios (
    id_usuario CHAR(26) PRIMARY KEY,
    nombre_usuario VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    codigo_contacto CHAR(6) NOT NULL UNIQUE
);

CREATE TABLE grupos (
    id_grupo CHAR(26) PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL UNIQUE,
    id_fundador CHAR(26) NOT NULL,

    FOREIGN KEY (id_fundador)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE invitaciones_directas (
    id_usuario CHAR(26),
    id_contacto CHAR(26),

    UNIQUE KEY invitacion_directa (id_usuario, id_contacto),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_contacto)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE invitaciones_grupales (
    id_usuario CHAR(26),
    id_grupo CHAR(26),

    UNIQUE KEY invitacion_grupo (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE contactos_directos (
    id_usuario CHAR(26),
    id_contacto CHAR(26),

    UNIQUE KEY contacto_directo (id_usuario, id_contacto),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_contacto)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE contactos_grupales (
    id_usuario CHAR(26),
    id_grupo CHAR(26),

    UNIQUE KEY contacto_grupal (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE mensajes (
    id_mensaje CHAR(26) PRIMARY KEY,
    contenido TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    id_emisor CHAR(26) NOT NULL,
    id_contacto CHAR(26) NULL,
    id_grupo CHAR(26) NULL,

    FOREIGN KEY (id_emisor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_contacto)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE SET NULL
);

CREATE TABLE ultimos_mensajes_leidos_directos (
    id_usuario CHAR(26) NOT NULL,
    id_contacto CHAR(26) NOT NULL,
    id_mensaje CHAR(26) NOT NULL,

    UNIQUE KEY unico_privado (id_usuario, id_contacto),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_contacto)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_grupales (
    id_usuario CHAR(26) NOT NULL,
    id_grupo CHAR(26) NOT NULL,
    id_mensaje CHAR(26) NULL,

    UNIQUE KEY unico_grupo (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

-- CREATE TABLE conexion_directa (
--     id_usuario INT UNSIGNED NOT NULL,
--     id_contacto INT UNSIGNED NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_directo (id_usuario, id_contacto),

--     FOREIGN KEY (id_usuario)
--         REFERENCES usuarios(id_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (id_contacto)
--         REFERENCES usuarios(id_usuario)
--         ON DELETE CASCADE
-- );

-- CREATE TABLE conexion_grupal (
--     id_usuario INT UNSIGNED NOT NULL,
--     id_grupo INT UNSIGNED NOT NULL,
--     last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY conect_grupal (id_usuario, id_grupo),

--     FOREIGN KEY (id_usuario)
--         REFERENCES usuarios(id_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (id_grupo)
--         REFERENCES grupos(id_grupo)
--         ON DELETE CASCADE
-- );
