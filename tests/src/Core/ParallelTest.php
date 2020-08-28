<?php declare(strict_types=1);

namespace src\Core;

use Klapuch\Lock\SemaphoreFactory;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$pid = pcntl_fork();
if ($pid === -1) {
	throw new \RuntimeException;
} elseif ($pid > 0) {
	$mutex = (new SemaphoreFactory)->createMutex('foo');
	usleep(100000);
	Assert::false($mutex->tryAcquire());
	Assert::false($mutex->tryRelease());
	$mutex->acquire();
	Assert::true(true);
	$mutex->release();

	pcntl_wait($status); //Protect against Zombie children
	exit(0);
} else {
	$mutex = (new SemaphoreFactory)->createMutex('foo');
	$mutex->acquire();
	usleep(100000);
	Assert::true(true);
	$mutex->release();
}

Assert::true(true);
