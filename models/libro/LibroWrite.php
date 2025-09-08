<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';
require_once __DIR__ . '/../universal/FileManager.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private readonly int $idLibro;
	private readonly string $titulo;
	private readonly string $descripcion;
	private readonly int $paginas;
	private readonly string $fechaPublicacion;
	private readonly int $idCategoria;

	private readonly ?array $portada;
	private readonly bool $eliminarPortada;

	private const string FOLDER = 'libros/';

	private const string SQL_COLUMN = 'portada';
	private const string SQL_TABLE = 'libros';
	private const string SQL_PRIMARY_KEY = 'id_libro';

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getIdLibro(): int
	{
		return $this->idLibro;
	}

	private function getTitulo(): string
	{
		return $this->titulo;
	}

	private function getDescripcion(): string
	{
		return $this->descripcion;
	}

	private function getPaginas(): int
	{
		return $this->paginas;
	}

	private function getFechaPublicacion(): string
	{
		return $this->fechaPublicacion;
	}

	private function getIdCategoria(): int
	{
		return $this->idCategoria;
	}

	private function getPortada(): ?array
	{
		return $this->portada;
	}

	private function getEliminarPortada(): bool
	{
		return $this->eliminarPortada;
	}

	// MARK: SETTERS

	private function nameEqualizer(string $property): string
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property));
	}

	private function setIdLibro(): void
	{
		$errorMessage = "El id del recurso debe ser un número entero superior o igual a 1 y solo contener números.";

		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$segments = explode('/', trim($path, '/'));
		$value = end($segments);

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->idLibro = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setPortada(): void
	{
		$name = 'portada';

		$filesUploaded = FileManager::flattenFilesArray($name);

		if (count($filesUploaded) === 0) {
			$this->portada = null;
			return;
		}

		if (count($filesUploaded) > 1) {
			$this->setValidationError('Solo se puede subir una imagen.');
			return;
		}

		$portada = $filesUploaded[0];

		$fileType = exif_imagetype($portada['tmp_name']);
		$allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

		if (!in_array($fileType, $allowedTypes)) {
			$this->setValidationError("Solo se permiten imágenes JPEG y PNG.");
		}

		if ($portada['size'] > 1048576) {
			$this->setValidationError('La imagen no puede superar 1MB.');
		}

		$this->portada = $portada;
	}

	private function setString(string $property): void
	{
		$name = $this->nameEqualizer($property);
		$value = $_POST[$name] ?? "";

		$errorMessage = "El campo '$name' no puede estar vacío.";

		$value === ""
			? $this->setValidationError($errorMessage)
			: $this->$property = $value;
	}

	private function setInt(string $property, int $min = 1): void
	{
		$name = $this->nameEqualizer($property);
		$value = $_POST[$name] ?? "";

		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => $min)))
			? $this->$property = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setDate(string $property): void
	{
		$name = $this->nameEqualizer($property);
		$value = $_POST[$name] ?? "";

		$dateFormat = 'Y-m-d';
		$errorMessage = "El campo '$name' debe tener el formato '$dateFormat'.";

		$dateTime = DateTime::createFromFormat($dateFormat, $value);

		$dateTime === false || $dateTime->format($dateFormat) !== $value
			? $this->setValidationError($errorMessage)
			: $this->$property = $value;
	}

	private function setBool(string $property): void
	{
		$name = $this->nameEqualizer($property);
		$value = $_POST[$name] ?? false;
		$errorMessage = "El campo '$name' solo es válido si está vacío.";

		if ($value === false) {
			$this->$property = false;
			return;
		}

		$value === ""
			? $this->$property = true
			: $this->setValidationError($errorMessage);
	}

	// MARK: CREATE

	public function createLibro(): void
	{
		$this->setString('titulo');
		$this->setString('descripcion');
		$this->setInt('paginas');
		$this->setDate('fechaPublicacion');
		$this->setInt('idCategoria');

		$this->setPortada();

		$this->checkValidationErrors();

		$createLibro = new FileManager();

		$portada = $this->getPortada();
		$createLibro->setFile($portada);
		$createLibro->setExtraDirectories(self::FOLDER);

		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();
		$portadaName = $createLibro->uploadFileName();

		$this->tituloExists($titulo);
		$this->idCategoriaExists($idCategoria);

		$statement =
			"INSERT INTO libros (
				titulo,
				descripcion,
				portada,
				paginas,
				fecha_publicacion,
				id_categoria
			)
			VALUES (
				?,
				?,
				?,
				?,
				?,
				?
			)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"sssisi",
			$titulo,
			$descripcion,
			$portadaName,
			$paginas,
			$fechaPublicacion,
			$idCategoria
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Libro creado");
		$createLibro->uploadFile();
		$this->getResponse();
	}

	// MARK: UPDATE

	public function updateLibro(): void
	{
		$this->setIdLibro();
		$this->setString('titulo');
		$this->setString('descripcion');
		$this->setInt('paginas');
		$this->setDate('fechaPublicacion');
		$this->setInt('idCategoria');
		$this->setPortada();
		$this->setBool('eliminarPortada');

		$this->checkValidationErrors();

		$idLibro = $this->getIdLibro();
		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();
		$portada = $this->getPortada();
		$eliminarPortada = $this->getEliminarPortada();

		$this->tituloUpdateExists($titulo, $idLibro);
		$this->idLibroExists($idLibro);
		$this->idCategoriaExists($idCategoria);

		$updateLibro = new FileManager();

		$updateLibro->setFile($portada);
		$updateLibro->setDeleteCheckbox($eliminarPortada);
		$updateLibro->setExtraDirectories(self::FOLDER);

		$portadaName = $updateLibro->updateFileName(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);
		$libroPath = $updateLibro->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);

		$statement =
			"UPDATE libros
				SET titulo = ?,
				descripcion = ?,
				portada = ?,
				paginas = ?,
				fecha_publicacion = ?,
				id_categoria = ?
			WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"sssisii",
			$titulo,
			$descripcion,
			$portadaName,
			$paginas,
			$fechaPublicacion,
			$idCategoria,
			$idLibro
		);

		$query->execute();

		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$this->setStatus(200);
			$this->setMessage('¡Libro modificado!');
		} else {
			$this->setStatus(204);
		}

		$updateLibro->updateFile($libroPath);
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteLibro(): void
	{
		$this->setIdLibro();

		$this->checkValidationErrors();

		$deleteLibro = new FileManager();

		$idLibro = $this->getIdLibro();
		$libroPath = $deleteLibro->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);

		$statement =
			"DELETE FROM libros
			WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$idLibro
		);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$this->setStatus(204);
			$deleteLibro->deleteFile($libroPath);
		} else {
			$this->setStatus(404);
			$this->setMessage('¡El libro solicitado no existe!');
		}

		$this->getResponse();
	}

	// MARK: DELETE ALL

	public function deleteAllLibros(): void
	{
		$deleteAllLibros = new FileManager();
		$deleteAllLibros->setExtraDirectories(self::FOLDER);

		$statement =
			"DELETE FROM libros";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas > 0) {
			$deleteAllLibros->deleteAllFiles();
			$this->setStatus(204);
			$this->getResponse();
		} else {
			$this->setStatus(404);
			$this->setIntegrityError('¡No hay ningún libro para eliminar!');
			$this->checkIntegrityErrors();
		}
	}
}
