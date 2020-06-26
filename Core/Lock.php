<?php declare(strict_types=1);

namespace Klapuch\Lock;

use Klapuch\Lock\Exceptions\AcquireException;
use Klapuch\Lock\Exceptions\ZombieException;

abstract class Lock implements Lockable
{
	/** @var string */
	private $name;


	public function __construct(string $name)
	{
		$this->name = $name;
	}


	/**
	 * @param float $interval [seconds]
	 */
	public function wait(float $interval = 1.0): void
	{
		$maxTime = time() + 3600;
		$i = 0;
		do {
			if ($i > 0) {
				if (time() > $maxTime) {
					throw new ZombieException('Too long wait for exclusive lock.');
				}
				usleep((int) ($interval * 1E6));
			}
			$lock = $this->tryAcquire();
			++$i;
		} while (!$lock);
	}


	public function release(): void
	{
		if (!$this->tryRelease()) {
			throw new AcquireException(sprintf('First you must acquire "%s".', $this->getName()));
		}
	}


	/**
	 * @param callable $callback
	 * @param float $interval [seconds]
	 * @return mixed
	 */
	public function synchronized(callable $callback, float $interval = 1.0)
	{
		try {
			$this->wait($interval);
			return $callback();
		} finally {
			$this->release();
		}
	}


	final protected function getName(): string
	{
		return $this->name;
	}

}
