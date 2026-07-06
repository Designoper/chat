<?php

declare(strict_types=1);

final class SSEState
{
	public array $eventBuffer = [];
	public int $eventId = 1;

	public function __construct() {}

	// ============================================================================
	// MARK: STORE EVENT
	// ============================================================================
	public function storeEvent(string $event, mixed $data): int
	{
		$id = $this->eventId;

		$this->eventBuffer[] = [
			'id'    => $id,
			'event' => $event,
			'data'  => $data,
		];

		$this->eventId = $id + 1;

		// Mantener solo los últimos 100 eventos
		if (count($this->eventBuffer) > 100) {
			array_shift($this->eventBuffer);
		}

		return $id;
	}
}
