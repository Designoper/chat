DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

-- TABLA USUARIOS
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Índice para acelerar el login (búsqueda por nombre)
CREATE INDEX idx_usuario_nombre ON usuarios(nombre);

-- TABLA MENSAJES
CREATE TABLE mensajes (
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- Índice para acelerar la carga de mensajes por usuario
CREATE INDEX idx_mensajes_usuario ON mensajes(id_usuario);

-- TABLA MENSAJES DIRECTOS
CREATE TABLE mensajes_directos (
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    FOREIGN KEY (id_emisor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    FOREIGN KEY (id_receptor)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- Índices para acelerar chats privados
CREATE INDEX idx_md_emisor ON mensajes_directos(id_emisor);
CREATE INDEX idx_md_receptor ON mensajes_directos(id_receptor);
