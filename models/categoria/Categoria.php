<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Categoria extends ApiResponse
{
	public function __construct()
	{
		parent::__construct();
	}

	public function readCategorias(): void
	{
		$statement =
			"SELECT
				categorias.id_categoria AS id,
				categorias.categoria
			FROM categorias
			ORDER BY categorias.categoria";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();

		$categorias = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$categorias
			? 'Categorias obtenidas'
			: 'No hay categorias';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($categorias);
		// header('Cache-Control: public, max-age=31536000, must-revalidate');
		$this->getResponse();
	}
}
