<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

abstract class LibroIntegrityErrors extends ApiResponse
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function librosExists(): void
	{
		$statement =
			"SELECT 1
			FROM libros
			LIMIT 1";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_assoc();

		$query->close();

		if (!$libros) {
			$this->setStatus(404);
			$this->setIntegrityError('¡No hay ningún libro!');
			$this->checkIntegrityErrors();
		}
	}

	protected function tituloExists(string $titulo): void
	{
		$statement =
			"SELECT 1
			FROM libros
			WHERE titulo = ?
			LIMIT 1";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("s", $titulo);
		$query->execute();

		$libro = $query->get_result()->fetch_assoc();

		$query->close();

		if ($libro) {
			$this->setStatus(409);
			$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
			$this->checkIntegrityErrors();
		}
	}

	protected function tituloUpdateExists(string $titulo, int $idLibro): void
	{
		$statement =
			"SELECT 1
			FROM libros
			WHERE titulo = ?
			AND id_libro != ?
			LIMIT 1";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("si", $titulo, $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_assoc();

		$query->close();

		if ($libro) {
			$this->setStatus(409);
			$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
			$this->checkIntegrityErrors();
		}
	}

	protected function idLibroExists(int $idLibro): void
	{
		$statement =
			"SELECT 1
			FROM libros
			WHERE id_libro = ?
			LIMIT 1";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_assoc();

		$query->close();

		if (!$libro) {
			$this->setStatus(404);
			$this->setIntegrityError('¡El libro solicitado no existe!');
			$this->checkIntegrityErrors();
		}
	}

	protected function idCategoriaExists(int $idCategoria): void
	{
		$statement =
			"SELECT 1
			FROM categorias
			WHERE id_categoria = ?
			LIMIT 1";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idCategoria);
		$query->execute();

		$categoria = $query->get_result()->fetch_assoc();

		$query->close();

		if (!$categoria) {
			$this->setStatus(404);
			$this->setIntegrityError('¡La categoria seleccionada no existe!');
			$this->checkIntegrityErrors();
		}
	}
}
