-- DROP DATABASE IF EXISTS chat;
-- CREATE DATABASE chat CHARACTER SET utf8mb4;
-- USE chat;

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
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
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
);

CREATE TABLE conexion (
    id_ultima_conexion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_receptor INT NOT NULL DEFAULT 0,
    id_grupo INT NOT NULL DEFAULT 0,
    -- ultima_conexion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_conexion TIMESTAMP NOT NULL
    DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY ux_conexion (id_usuario, id_receptor, id_grupo),
    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
    -- FOREIGN KEY (id_receptor)
    --     REFERENCES usuarios(id_usuario)
    --     ON DELETE CASCADE,
    -- FOREIGN KEY (id_grupo)
    --     REFERENCES grupos(id_grupo)
    --     ON DELETE CASCADE
);

-- CREATE TABLE ultima_conexion (
--     id_usuario INT NOT NULL,
--     ultima_conexion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     id_receptor INT NULL,
--     id_grupo INT NULL,

--     PRIMARY KEY (id_usuario, id_receptor, id_grupo),


--     FOREIGN KEY (id_usuario)
--         REFERENCES usuarios(id_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (id_receptor)
--         REFERENCES usuarios(id_usuario)
--         ON DELETE CASCADE,

--     FOREIGN KEY (id_grupo)
--         REFERENCES grupos(id_grupo)
--         ON DELETE CASCADE
-- );

-- CREATE INDEX idx_mensajes_emisor ON mensajes(id_emisor);
-- CREATE INDEX idx_mensajes_receptor ON mensajes(id_receptor);
