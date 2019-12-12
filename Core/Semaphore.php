<?php declare(strict_types = 1);

namespace Klapuch\Lock;

final class Semaphore {
	/** @var string */
	private $name;

	/** @var resource|null */
	private $handler;

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function acquire(): void {
		$this->handler = $this->createResource();
		if (!sem_acquire($this->handler)) {
			throw new \RuntimeException(sprintf('Can not acquire "%s"', $this->name));
		}
	}

	public function tryAcquire(): bool {
		$handler = $this->createResource();
		if (!sem_acquire($handler, true)) {
			return false;
		}
		$this->handler = $handler;
		return true;
	}

	public function release(): void {
		if ($this->handler === null) {
			throw new \RuntimeException(sprintf('First you must acquire "%s"', $this->name));
		}
		if (!sem_release($this->handler)) {
			throw new \RuntimeException(sprintf('Can not release "%s"', $this->name));
		}
		$this->handler = null;
	}

	/**
	 * @param callable $callback
	 * @return mixed
	 */
	public function synchronized(callable $callback) {
		try {
			$this->acquire();
			return $callback();
		} finally {
			$this->release();
		}
	}

	/**
	 * @return resource
	 */
	private function createResource() {
		if ($this->handler !== null) {
			throw new \RuntimeException(sprintf('Semaphore "%s" is already acquired', $this->name));
		}
		/** @var resource|false $handler */
		$handler = sem_get(self::key($this->name));
		if ($handler === false) {
			throw new \RuntimeException(sprintf('Can not get semaphore "%s"', $this->name));
		}
		return $handler;
	}

	private static function key(string $name): int {
		return crc32($name);
	}
}
