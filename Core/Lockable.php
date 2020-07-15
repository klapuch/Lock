<?php declare(strict_types=1);

namespace Klapuch\Lock;

use Klapuch\Lock\Exceptions\AcquireException;

interface Lockable
{

	/**
	 * @throws AcquireException
	 */
	function acquire(): void;


	function tryAcquire(): bool;


	/**
	 * @throws AcquireException
	 */
	function release(): void;


	function tryRelease(): bool;


	/** destroy resource */
	function destroy(): void;

}
