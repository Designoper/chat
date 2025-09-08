<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';
require_once __DIR__ . '/../universal/FileManager.php';

final class LibroRead extends ApiResponse
{
	private readonly string $statement;
	private readonly int $idLibro;

	private array $params = [];
	private array $types = [];

	private readonly ?int $minimoPaginas;
	private readonly ?int $maximoPaginas;
	private readonly ?string $minimoFechaPublicacion;
	private readonly ?string $maximoFechaPublicacion;
	private readonly ?string $titulo;
	private readonly ?int $idCategoria;
	private readonly ?string $criterioOrden;

	public function __construct()
	{
		parent::__construct();

		$host = $this->getHost();
		$defaultImage = FileManager::DEFAULT_IMAGE;

		$this->setStatement(
			"SELECT
				libros.id_libro AS id,
				libros.titulo,
				CASE
					WHEN libros.portada IS NULL THEN CONCAT('$host', '$defaultImage')
					ELSE CONCAT('$host', libros.portada)
				END AS portada,
				libros.descripcion,
				libros.paginas,
				libros.fecha_publicacion,
				categorias.categoria
			FROM libros
			NATURAL JOIN categorias
			WHERE 1=1"
		);
	}

	// MARK: GETTERS

	private function getStatement(): string
	{
		return $this->statement;
	}

	private function getIdLibro(): int
	{
		return $this->idLibro;
	}

	private function getTitulo(): ?string
	{
		return $this->titulo;
	}

	private function getMinimoPaginas(): ?int
	{
		return $this->minimoPaginas;
	}

	private function getMaximoPaginas(): ?int
	{
		return $this->maximoPaginas;
	}

	private function getMinimoFechaPublicacion(): ?string
	{
		return $this->minimoFechaPublicacion;
	}

	private function getMaximoFechaPublicacion(): ?string
	{
		return $this->maximoFechaPublicacion;
	}

	private function getIdCategoria(): ?int
	{
		return $this->idCategoria;
	}

	private function getCriterioOrden(): ?string
	{
		return $this->criterioOrden;
	}

	private function getParams(): array
	{
		return $this->params;
	}

	private function getTypes(): array
	{
		return $this->types;
	}

	// MARK: SETTERS

	private function setStatement(string $statement): void
	{
		$this->statement = $statement;
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

	private function setMinimoPaginas(): void
	{
		$name = 'min_paginas';
		$value = $_GET[$name] ?? "";
		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		if ($value === "") {
			$this->minimoPaginas = null;
			return;
		}

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->minimoPaginas = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setMaximoPaginas(): void
	{
		$name = 'max_paginas';
		$value = $_GET[$name] ?? "";
		$errorMessage = "El campo '$name' debe tener el formato yyyy-mm-dd.";

		if ($value === "") {
			$this->maximoPaginas = null;
			return;
		}

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->maximoPaginas = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setMinimoFechaPublicacion(): void
	{
		$name = 'min_fecha';
		$value = $_GET[$name] ?? "";
		$dateFormat = 'Y-m-d';
		$errorMessage = "El campo '$name' debe tener el formato '$dateFormat'.";

		if ($value === "") {
			$this->minimoFechaPublicacion = null;
			return;
		}

		$dateTime = DateTime::createFromFormat($dateFormat, $value);

		$dateTime === false || $dateTime->format($dateFormat) !== $value
			? $this->setValidationError($errorMessage)
			: $this->minimoFechaPublicacion = $value;
	}

	private function setMaximoFechaPublicacion(): void
	{
		$name = 'max_fecha';
		$value = $_GET[$name] ?? "";
		$dateFormat = 'Y-m-d';
		$errorMessage = "El campo '$name' debe tener el formato '$dateFormat'.";

		if ($value === "") {
			$this->maximoFechaPublicacion = null;
			return;
		}

		$dateTime = DateTime::createFromFormat($dateFormat, $value);

		$dateTime === false || $dateTime->format($dateFormat) !== $value
			? $this->setValidationError($errorMessage)
			: $this->maximoFechaPublicacion = $value;
	}

	private function setTitulo(): void
	{
		$name = 'titulo';
		$value = $_GET[$name] ?? "";

		$value === ""
			? $this->titulo = null
			: $this->titulo = $value;
	}

	private function setIdCategoria(): void
	{
		$name = 'id_categoria';
		$value = $_GET[$name] ?? "";
		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		if ($value === "") {
			$this->idCategoria = null;
			return;
		}

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->idCategoria = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setCriterioOrden(): void
	{
		$name = 'criterio_orden';
		$value = $_GET[$name] ?? "";
		$permitedValues = [
			'tituloAsc',
			'tituloDesc',
			'paginasAsc',
			'paginasDesc',
			'fechaAsc',
			'fechaDesc'
		];

		$errorMessage = "El campo '$name' solo acepta los siguientes valores: " . implode(",", $permitedValues) . ".";

		if ($value === "") {
			$this->criterioOrden = null;
			return;
		}

		in_array($value, $permitedValues, true)
			? $this->criterioOrden = $value
			: $this->setValidationError($errorMessage);
	}

	private function setParam(string|int $param): void
	{
		$this->params[] = $param;
	}

	private function setType(string $type): void
	{
		$this->types[] = $type;
	}

	// MARK: READ ALL

	public function readLibros(): void
	{
		$statement = $this->getStatement();

		$statement .= " ORDER BY libros.titulo";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos.'
			: 'No hay ningún libro.';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		// header('Cache-Control: public, max-age=31536000, must-revalidate');
		$this->getResponse();
	}

	// MARK: READ ONE

	public function readLibro(): void
	{
		$this->setIdLibro();
		$this->checkValidationErrors();

		$idLibro = $this->getIdLibro();
		$statement = $this->getStatement();

		$statement .= " AND libros.id_libro = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_assoc();

		$query->close();

		if ($libro) {
			$this->setStatus(200);
			$this->setMessage('Libro obtenido.');
			$this->setContent($libro);
			$this->getResponse();
		} else {
			$this->setStatus(404);
			$this->setIntegrityError('¡El libro solicitado no existe!');
			$this->checkIntegrityErrors();
		}
	}

	// MARK: FILTER

	public function filterLibros(): void
	{
		$this->setMinimoPaginas();
		$this->setMaximoPaginas();
		$this->setMinimoFechaPublicacion();
		$this->setMaximoFechaPublicacion();
		$this->setTitulo();
		$this->setIdCategoria();
		$this->setCriterioOrden();

		$this->checkValidationErrors();

		$statement = $this->getStatement();

		if ($this->getMinimoPaginas()) {
			$this->setParam($this->getMinimoPaginas());
			$this->setType('i');
			$statement .= " AND libros.paginas >= ?";
		}

		if ($this->getMaximoPaginas()) {
			$this->setParam($this->getMaximoPaginas());
			$this->setType('i');
			$statement .= " AND libros.paginas <= ?";
		}

		if ($this->getMinimoFechaPublicacion()) {
			$this->setParam($this->getMinimoFechaPublicacion());
			$this->setType('s');
			$statement .= " AND libros.fecha_publicacion >= ?";
		}

		if ($this->getMaximoFechaPublicacion()) {
			$this->setParam($this->getMaximoFechaPublicacion());
			$this->setType('s');
			$statement .= " AND libros.fecha_publicacion <= ?";
		}

		if ($this->getTitulo()) {
			$this->setParam("%" . $this->getTitulo() . "%");
			$this->setType('s');
			$statement .= " AND libros.titulo LIKE ?";
		}

		if ($this->getIdCategoria()) {
			$this->setParam($this->getIdCategoria());
			$this->setType('i');
			$statement .= " AND libros.id_categoria = ?";
		}

		if ($this->getCriterioOrden()) {

			$orderMap = [
				'tituloAsc' => "libros.titulo ASC",
				'tituloDesc' => "libros.titulo DESC",
				'paginasAsc' => "libros.paginas ASC",
				'paginasDesc' => "libros.paginas DESC",
				'fechaAsc' => "libros.fecha_publicacion ASC",
				'fechaDesc' => "libros.fecha_publicacion DESC"
			];

			$statement .= " ORDER BY " . $orderMap[$this->getCriterioOrden()];
		} else {
			$statement .= " ORDER BY libros.titulo ASC";
		}

		$query = $this->getConnection()->prepare($statement);

		$params = $this->getParams();

		if (count($params) > 0) {
			$types = $this->getTypes();
			$types = implode($types);
			$query->bind_param($types, ...$params);
		}

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos.'
			: 'Ningún libro coincide con el criterio seleccionado.';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		// header('Cache-Control: public, max-age=31536000, must-revalidate');
		$this->getResponse();
	}
}
