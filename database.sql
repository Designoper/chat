DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

SET default_storage_engine=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE grupos (
    id_grupo INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nombre_grupo VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE membresias (
    id_usuario INT UNSIGNED,
    id_grupo INT UNSIGNED,
    rol ENUM ('fundador','miembro','pendiente') NOT NULL,

    PRIMARY KEY (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE mensajes (
    id_mensaje INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    id_emisor INT UNSIGNED NOT NULL,
    id_receptor INT UNSIGNED NULL,
    id_grupo INT UNSIGNED NULL,

    FOREIGN KEY (id_emisor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE SET NULL
);

CREATE TABLE conexion_directa (
    id_usuario INT UNSIGNED NOT NULL,
    id_receptor INT UNSIGNED NOT NULL,
    last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY conect_directo (id_usuario, id_receptor),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE conexion_grupal (
    id_usuario INT UNSIGNED NOT NULL,
    id_grupo INT UNSIGNED NOT NULL,
    last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY conect_grupal (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_directos (
    id_usuario INT UNSIGNED NOT NULL,
    id_receptor INT UNSIGNED NOT NULL,
    id_mensaje INT UNSIGNED NULL,

    UNIQUE KEY unico_privado (id_usuario, id_receptor),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_grupales (
    id_usuario INT UNSIGNED NOT NULL,
    id_grupo INT UNSIGNED NOT NULL,
    id_mensaje INT UNSIGNED NULL,

    UNIQUE KEY unico_grupo (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

-- CREATE TABLE ultimos_mensajes_leidos (
--     id_usuario INT UNSIGNED NOT NULL,
--     tipo ENUM('publico','directo','grupo') NOT NULL,
--     id_objetivo INT UNSIGNED NULL, -- receptor o grupo
--     id_mensaje INT NULL,

--     UNIQUE KEY unico (id_usuario, tipo, id_objetivo)
-- );
