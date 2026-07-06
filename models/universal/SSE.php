<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/SSEState.php';

abstract readonly class SSE extends Sanitizer
{
	private ?int $lastId;

	protected function __construct()
	{
		parent::__construct();
	}

	// ============================================================================
	// MARK: GET LAST EVENT ID
	// ============================================================================
	private function getLastEventId(): void
	{
		if (isset($_SERVER['HTTP_LAST_EVENT_ID'])) {
			$this->lastId = (int) $_SERVER['HTTP_LAST_EVENT_ID'];
		} else $this->lastId = null;
	}

	// ============================================================================
	// MARK: RESEND MISSED EVENTS
	// ============================================================================
	private function resendMissedEvents(callable $function): void
	{
		$lastId = $this->lastId;

		if ($lastId === null) {
			return; // primera conexión
		}

		$function();
	}

	// ============================================================================
	// MARK: SET SSE
	// ============================================================================
	protected function setSSE(callable $function): void
	{
		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
		header("Connection: keep-alive");
		header("Content-Encoding: none");
		header("X-Accel-Buffering: no");

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		set_time_limit(0);
		ignore_user_abort(false);

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		$this->getLastEventId();

		// $this->resendMissedEvents($function);

		while (true) {

			if (connection_aborted()) {
				break;
			}

			$function();

			$this->heartbeat();
			$this->doubleFlush();

			usleep(300000); // 0.3s
		}
	}

	// ============================================================================
	// MARK: HEARTBEAT
	// ============================================================================
	private function heartbeat(): void
	{
		static $lastPing = 0;

		if (time() - $lastPing > 10) {
			$lastPing = time();
			echo ": keepalive\n\n";
			$this->doubleFlush();
		}
	}

	// ============================================================================
	// MARK: DOUBLE FLUSH
	// ============================================================================
	private function doubleFlush(): void
	{
		if (ob_get_level() > 0) {
			ob_flush();
		}

		flush();
	}

	// ============================================================================
	// MARK: SEND EVENT
	// ============================================================================
	protected function sendEvent(string $event, mixed $data, ?int $id = null): void
	{
		// $id = $this->state->storeEvent($event, $data);

		if ($id) {
			echo "id: $id\n";
		}
		echo "event: $event\n";
		echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

		$this->doubleFlush();
	}
}
