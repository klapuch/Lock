<?php declare(strict_types=1);

namespace Klapuch\Lock;

use Klapuch\Lock\Exceptions\AcquireException;

abstract class Lock implements Lockable
{
	/** @var string */
	private $name;


	public function __construct(string $name)
	{
		$this->name = $name;
	}


	public function release(): void
	{
		if (!$this->tryRelease()) {
			throw new AcquireException(sprintf('First you must acquire "%s".', $this->getName()));
		}
	}


	/**
	 * @return mixed
	 */
	public function synchronized(\Closure $onCriticalSection)
	{
		try {
			$this->wait();
			return $onCriticalSection();
		} finally {
			$this->release();
		}
	}


	final protected function getName(): string
	{
		return $this->name;
	}


	/**
	 * Here you can implement active waiting, if you locking mechanism does not support better way
	 */
	protected function wait(): void
	{
		$this->acquire();
	}


	public function __destruct()
	{
		$this->destroy();
	}

}
