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
			"SELECT 1 FROM usuarios
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
}
