<?php declare(strict_types=1);

namespace Klapuch\Lock;

use Klapuch\Lock\Exceptions;

final class Semaphore extends Lock
{
	/** @var int */
	private $maxAcquire;

	/** @var int */
	private $permission;

	/** @var resource|null */
	private $handler;

	/**
	 * deadlock protection, in one process call twice acquire()
	 * @var bool
	 */
	private $acquired = false;


	public function __construct(string $name, int $maxAcquire, int $permission = 0666)
	{
		parent::__construct($name);
		$this->maxAcquire = $maxAcquire;
		$this->permission = $permission;
	}


	public function acquire(): void
	{
		if ($this->acquired) {
			throw new Exceptions\AcquireException(sprintf('Semaphore "%s" is already acquired.', $this->getName()));
		}
		if (!sem_acquire($this->getResource())) {
			throw new Exceptions\AcquireException(sprintf('Can not acquire "%s".', $this->getName()));
		}
		$this->acquired = true;
	}


	public function tryAcquire(): bool
	{
		$acquired = sem_acquire($this->getResource(), true);
		if ($acquired) {
			$this->acquired = true;
		}
		return $acquired;
	}


	public function tryRelease(): bool
	{
		$this->acquired = $release = false;
		if ($this->handler !== null) {
			$release = @sem_release($this->handler); // intentionally @
		}
		return $release;
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
			throw new Exceptions\CreateLockException(sprintf('Can not get semaphore "%s".', $this->getName()));
		}
		return $handler;
	}


	private static function key(string $name): int
	{
		return crc32($name);
	}

}
