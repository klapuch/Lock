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

		$semaphore->destroy();
		Assert::true($semaphore->synchronized(function () {
			return true;
		}));
	}


	public function testTryAcquire()
	{
		$factory = new SemaphoreFactory();

		$mutex = $factory->createMutex(__METHOD__);
		$mutex2 = $factory->createMutex(__METHOD__);
		Assert::true($mutex->tryAcquire());

		Assert::false($mutex2->tryAcquire());
		unset($mutex2);
		$mutex->release();
	}

}

(new SemaphoreTest())->run();
