<?php

declare(strict_types=1);

final class SSEState
{
	public array $eventBuffer = [];
	public int $nextEventId = 1;

	public function __construct() {}

	// ============================================================================
	// MARK: STORE EVENT
	// ============================================================================
	public function storeEvent(string $event, mixed $data): int
	{
		$id = $this->nextEventId++;

		$this->eventBuffer[$id] = [
			"id"    => $id,
			"event" => $event,
			"data"  => $data,
		];

		// Mantener solo los últimos 100 eventos
		if (count($this->eventBuffer) > 100) {
			array_shift($this->eventBuffer);
		}

		return $id;
	}
}
