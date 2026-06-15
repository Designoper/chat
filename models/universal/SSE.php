<?php

declare(strict_types=1);

require_once __DIR__ . '/Response.php';

abstract readonly class SSE extends Response
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function setSSE(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		set_time_limit(0);
		ignore_user_abort(false);

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
	}

	protected function heartbeat(): void
	{
		static $lastPing = 0;

		if (time() - $lastPing > 10) {
			$this->keepAlive();
			$lastPing = time();
		}
	}

	protected function sendEvent(string $event, mixed $data): void
	{
		echo "event: $event\n";
		echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
		flush();
	}

	protected function sendMessage(mixed $data): void
	{
		echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
		flush();
	}

	private function keepAlive(): void
	{
		echo ": keepalive\n\n";
		flush();
	}
}
