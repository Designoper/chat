DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

-- TABLA USUARIOS
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- TABLA MENSAJES (UNIFICADA)
CREATE TABLE mensajes (
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_emisor INT NOT NULL,
    id_receptor INT NULL,          -- NULL = mensaje público
    es_publico BOOLEAN NOT NULL DEFAULT 1,

    FOREIGN KEY (id_emisor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- ÍNDICES PARA RENDIMIENTO
CREATE INDEX idx_mensajes_emisor ON mensajes(id_emisor);
CREATE INDEX idx_mensajes_receptor ON mensajes(id_receptor);
CREATE INDEX idx_mensajes_publicos ON mensajes(es_publico);
