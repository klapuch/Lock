<?php declare(strict_types=1);

namespace Klapuch\Lock;

use Klapuch\Lock\Exceptions\AcquireException;
use Klapuch\Lock\Exceptions\CreateLockException;

final class Semaphore extends Lock
{
	/** @var int */
	private $maxAcquire;

	/** @var int */
	private $permission;

	/** @var resource|null */
	private $handler;

	/** @var bool */
	private $acquired = false;


	public function __construct(string $name, int $maxAcquire, int $permission)
	{
		parent::__construct($name);
		$this->maxAcquire = $maxAcquire;
		$this->permission = $permission;
	}


	public function acquire(): void
	{
		if ($this->acquired) {
			throw new AcquireException(sprintf('Semaphore "%s" is already acquired.', $this->getName()));
		}
		if (!sem_acquire($this->getResource())) {
			throw new AcquireException(sprintf('Can not acquire "%s".', $this->getName()));
		}
		$this->acquired = true;
	}


	public function tryAcquire(): bool
	{
		$this->acquired = true;
		return sem_acquire($this->getResource(), true);
	}


	public function tryRelease(): bool
	{
		if ($this->acquired === false) {
			return false;
		}
		$this->acquired = false;
		return sem_release($this->handler);
	}


	public function destroy(): void
	{
		$this->tryRelease();
		$this->handler = null;
	}


	/**
	 * @return resource
	 */
	private function getResource()
	{
		if ($this->handler === null) {
			$this->handler = $this->createResource();
		}
		return $this->handler;
	}


	/**
	 * @return resource
	 */
	private function createResource()
	{
		/** @var resource|false $handler */
		$handler = sem_get(self::key($this->getName()), $this->maxAcquire, $this->permission, 1);
		if ($handler === false) {
			throw new CreateLockException(sprintf('Can not get semaphore "%s".', $this->getName()));
		}
		return $handler;
	}


	private static function key(string $name): int
	{
		return crc32($name);
	}

}