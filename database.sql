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
	fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	-- id_usuario INT NOT NULL,
	-- FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
);

INSERT INTO usuarios (nombre, password) VALUES
("Juan", "1234"),
("Manolo", "12345");

INSERT INTO mensajes (contenido) VALUES
("Hola, ¿cómo estás?"),
("Estoy bien, gracias. ¿Y tú?"),
("Muy bien también.");