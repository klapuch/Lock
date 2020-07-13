<?php declare(strict_types=1);

namespace Klapuch\Lock;

final class SemaphoreFactory
{
	/** @var Semaphore[] */
	private $semaphores;


	public function createMutex(string $name, int $permission = 0666): Semaphore
	{
		return $this->createSemaphore($name, 1, $permission);
	}


	public function createSemaphore(string $name, int $maxAcquire, int $permission = 0666): Semaphore
	{
		$name .= ".$maxAcquire";
		if (!isset($this->semaphores[$name])) {
			$this->semaphores[$name] = new Semaphore($name, $maxAcquire, $permission);
		}
		return $this->semaphores[$name];
	}

}
