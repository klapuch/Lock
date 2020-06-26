<?php declare(strict_types=1);

namespace Klapuch\Lock;

final class SemaphoreFactory
{
	/** @var Semaphore[] */
	private $semaphores;


	public function createMutex(string $name): Semaphore
	{
		return $this->createSemaphore($name, 1);
	}


	public function createSemaphore(string $name, int $maxAcquire): Semaphore
	{
		$name .= ".$maxAcquire";
		if (!isset($this->semaphores[$name])) {
			$this->semaphores[$name] = new Semaphore($name, $maxAcquire);
		}
		return $this->semaphores[$name];
	}

}
