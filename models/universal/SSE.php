<?php

declare(strict_types=1);

require_once __DIR__ . '/Response.php';

abstract readonly class SSE extends Response
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function start(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		set_time_limit(0);
		ignore_user_abort(false);

		// Limpia buffers previos
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Headers SSE
		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
		header("Connection: keep-alive");
		header("X-Accel-Buffering: no");

		ini_set('output_buffering', 'off');
		ini_set('zlib.output_compression', 0);

		// Flush inicial
		echo str_pad('', 4096) . "\n";
		flush();
	}

	protected function sendEvent(string $event, mixed $data): void
	{
		echo "event: $event\n";
		echo "data: " . json_encode($data) . "\n\n";
		flush();
	}

	protected function sendMessage(mixed $data): void
	{
		echo "data: " . json_encode($data) . "\n\n";
		flush();
	}

	protected function keepAlive(): void
	{
		echo "keepalive\n\n";
		flush();
	}
}
