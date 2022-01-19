<?php

declare(strict_types=1);

namespace muqsit\asynciterator\util;

use Closure;
use pocketmine\timings\TimingsHandler;

/**
 * @phpstan-template T
 * @phpstan-template U
 * @phpstan-template V
 */
final class KeyValueTimedClosure{

	/**
	 * @param TimingsHandler $timings
	 * @param Closure $closure
	 *
	 * @phpstan-param Closure(T, U) : V $closure
	 */
	public function __construct(
		private TimingsHandler $timings,
		public Closure $closure
	){}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 *
	 * @phpstan-param T $key
	 * @phpstan-param U $value
	 * @phpstan-return V
	 */
	public function call(mixed $key, mixed $value) : mixed{
		$this->timings->startTiming();
		$return = ($this->closure)($key, $value);
		$this->timings->stopTiming();
		return $return;
	}
}