<?php declare(strict_types = 1);

namespace Klapuch\Lock;

final class SemaphoreFactory {
	/** @var Semaphore[] */
	private $semaphores;

	public function create(string $name): Semaphore {
		if (!isset($this->semaphores[$name])) {
			$this->semaphores[$name] = new Semaphore($name);
		}
		return $this->semaphores[$name];
	}
}
