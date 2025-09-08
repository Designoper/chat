<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

abstract class UsuarioIntegrityErrors extends ApiResponse
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function nombreUsuarioExists(string $nombre): void
	{
		$statement =
			"SELECT * FROM usuarios
		WHERE nombre = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("s", $nombre);
		$query->execute();

		$usuario = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($usuario) {
			$this->setStatus(409);
			$this->setIntegrityError('¡Este nombre de usuario ya existe!');
		}
	}

	// protected function tituloUpdateExists(string $titulo, int $idLibro): void
	// {
	// 	$statement =
	// 		"SELECT * FROM libros
	// 	WHERE titulo = ?
	// 	AND id_libro != ?";

	// 	$query = $this->getConnection()->prepare($statement);
	// 	$query->bind_param("si", $titulo, $idLibro);
	// 	$query->execute();

	// 	$libro = $query->get_result()->fetch_all(MYSQLI_ASSOC);

	// 	$query->close();

	// 	if ($libro) {
	// 		$this->setStatus(409);
	// 		$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
	// 	}
	// }

	// protected function idLibroExists(int $idLibro): void
	// {
	// 	$statement =
	// 		"SELECT * FROM libros
	// 	WHERE id_libro = ?";

	// 	$query = $this->getConnection()->prepare($statement);
	// 	$query->bind_param("i", $idLibro);
	// 	$query->execute();

	// 	$libro = $query->get_result()->fetch_all(MYSQLI_ASSOC);

	// 	$query->close();

	// 	if (!$libro) {
	// 		$this->setStatus(404);
	// 		$this->setIntegrityError('¡El libro solicitado no existe!');
	// 	}
	// }

	// protected function idCategoriaExists(int $idCategoria): void
	// {
	// 	$statement =
	// 		"SELECT * FROM categorias
	// 	WHERE id_categoria = ?";

	// 	$query = $this->getConnection()->prepare($statement);
	// 	$query->bind_param("i", $idCategoria);
	// 	$query->execute();

	// 	$categoria = $query->get_result()->fetch_all(MYSQLI_ASSOC);

	// 	$query->close();

	// 	if (!$categoria) {
	// 		$this->setStatus(404);
	// 		$this->setIntegrityError('¡La categoria seleccionada no existe!');
	// 	}
	// }
}
