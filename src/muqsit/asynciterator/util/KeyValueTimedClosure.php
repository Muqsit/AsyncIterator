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

	/** @var TimingsHandler */
	private $timings;

	/**
	 * @var Closure
	 *
	 * @phpstan-var Closure(T, U) : V
	 */
	public $closure;

	/**
	 * @param TimingsHandler $timings
	 * @param Closure $closure
	 *
	 * @phpstan-param Closure(T, U) : V $closure
	 */
	public function __construct(TimingsHandler $timings, Closure $closure){
		$this->timings = $timings;
		$this->closure = $closure;
	}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 *
	 * @phpstan-param T $key
	 * @phpstan-param U $value
	 * @phpstan-return V
	 */
	public function call($key, $value){
		$this->timings->startTiming();
		$return = ($this->closure)($key, $value);
		$this->timings->stopTiming();
		return $return;
	}
}