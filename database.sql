DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat CHARACTER SET utf8mb4;
USE chat;

CREATE TABLE usuarios (
	id_usuario INT PRIMARY KEY AUTO_INCREMENT,
	nombre VARCHAR(50) NOT NULL,
	password VARCHAR(50) NOT NULL
);

CREATE TABLE mensajes (
	id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
	contenido VARCHAR(500) NOT NULL,
	fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	id_usuario INT NOT NULL,
	FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
);

CREATE TABLE mensajes_directos (
	id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
	contenido VARCHAR(500) NOT NULL,
	fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	id_emisor INT NOT NULL,
	id_receptor INT NOT NULL,
	FOREIGN KEY (id_emisor) REFERENCES usuarios(id_usuario),
	FOREIGN KEY (id_receptor) REFERENCES usuarios(id_usuario)
);

INSERT INTO usuarios (nombre, password) VALUES
("test", "1"),
("test2", "2"),
("test3", "3");

INSERT INTO mensajes (contenido, id_usuario) VALUES
("Hola, ¿cómo estás?", 1),
("Muy bien, ¿y tú?", 2);