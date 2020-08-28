<?php declare(strict_types=1);

namespace src\Core;

require_once __DIR__ . '/../../bootstrap.php';

use Klapuch\Lock\Exceptions\AcquireException;
use Klapuch\Lock\Semaphore;
use Klapuch\Lock\SemaphoreFactory;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
final class SemaphoreTest extends TestCase
{

	public function testAlreadyAcquired(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		$semaphore->acquire();

		Assert::exception(static function () use ($semaphore): void {
			$semaphore->acquire();
		}, AcquireException::class);

		$semaphore->release();
	}


	public function testAlreadyReleased(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		$semaphore->acquire();
		$semaphore->release();
		Assert::exception(static function () use ($semaphore): void {
			$semaphore->release();
		}, AcquireException::class);
	}


	public function testTryFalse(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		$semaphore->acquire();
		Assert::false($semaphore->tryAcquire());
		$semaphore->release();
		Assert::false($semaphore->tryRelease());
	}


	public function testTryTrue(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		Assert::true($semaphore->tryAcquire());
		Assert::true($semaphore->tryRelease());
	}


	public function testAcquireRelease(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		$semaphore->acquire();
		$semaphore->release();
		Assert::true(true); // no exception
	}


	public function testTwiceTryAcquire(): void
	{
		$semaphore = self::createMutex(__METHOD__);

		Assert::true($semaphore->tryAcquire());
		Assert::false($semaphore->tryAcquire());
		$semaphore->release();
	}


	public function testTwiceTryRelease(): void
	{
		$mutex = self::createMutex(__METHOD__);

		$mutex->acquire();
		Assert::true($mutex->tryRelease());
		Assert::false($mutex->tryRelease());
	}


	public function testDestructor(): void
	{
		$mutex = self::createMutex(__METHOD__);
		$mutex2 = self::createMutex(__METHOD__);
		Assert::true($mutex->tryAcquire());

		Assert::false($mutex2->tryAcquire());
		unset($mutex2);
		$mutex->release();
	}


	public function testDestroy(): void
	{
		$mutex = self::createMutex(__METHOD__);
		$mutex2 = self::createMutex(__METHOD__);
		Assert::true($mutex->tryAcquire());

		Assert::false($mutex2->tryAcquire());
		$mutex2->destroy();
		$mutex->release();
	}


	private static function createMutex(string $name): Semaphore
	{
		return (new SemaphoreFactory())->createMutex($name);
	}

}

(new SemaphoreTest())->run();
