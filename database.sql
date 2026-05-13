DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

SET default_storage_engine=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
    -- estado ENUM ('conectado','desconectado') NOT NULL
);

CREATE TABLE grupos (
    id_grupo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_grupo VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE membresias (
    id_usuario INT,
    id_grupo INT,
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
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_emisor INT NOT NULL,
    id_receptor INT NULL,
    id_grupo INT NULL,

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

CREATE TABLE conexion_publica (
    id_usuario INT NOT NULL,
    conectado BOOLEAN,

    UNIQUE KEY conect_publico (id_usuario),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE conexion_directa (
    id_usuario INT NOT NULL,
    id_receptor INT NOT NULL,
    conectado BOOLEAN,

    UNIQUE KEY conect_directo (id_usuario, id_receptor),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE conexion_grupal (
    id_usuario INT NOT NULL,
    id_grupo INT NOT NULL,
    conectado BOOLEAN,

    UNIQUE KEY conect_grupal (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_publicos (
    id_usuario INT NOT NULL,
    id_mensaje INT NULL,

    UNIQUE KEY unico_publico (id_usuario),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_directos (
    id_usuario INT NOT NULL,
    id_receptor INT NOT NULL,
    id_mensaje INT NULL,

    UNIQUE KEY unico_privado (id_usuario, id_receptor),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE ultimos_mensajes_leidos_grupales (
    id_usuario INT NOT NULL,
    id_grupo INT NOT NULL,
    id_mensaje INT NULL,

    UNIQUE KEY unico_grupo (id_usuario, id_grupo),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

-- CREATE INDEX idx_mensajes_emisor ON mensajes(id_emisor);
-- CREATE INDEX idx_mensajes_receptor ON mensajes(id_receptor);
