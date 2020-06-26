<?php declare(strict_types=1);

namespace src\Core;

require_once __DIR__ . '/../../bootstrap.php';

use Klapuch\Lock\Exceptions\AcquireException;
use Klapuch\Lock\SemaphoreFactory;
use Tester\Assert;
use Tester\TestCase;

final class SemaphoreTest extends TestCase
{

	public function testBasic(): void
	{
		$factory = new SemaphoreFactory();
		$semaphore = $factory->createMutex('foo');

		$semaphore->acquire();
		Assert::false($semaphore->tryAcquire());

		Assert::exception(function () use ($semaphore) {
			$semaphore->acquire();
		}, AcquireException::class);

		$semaphore->release();
		Assert::false($semaphore->tryRelease());

		Assert::exception(function () use ($semaphore) {
			$semaphore->release();
		}, AcquireException::class);

		Assert::true($semaphore->tryAcquire());
		$semaphore->release();

		Assert::true($semaphore->synchronized(function () {
			return true;
		}));
	}

}

(new SemaphoreTest())->run();
