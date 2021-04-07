<?php

declare(strict_types=1);

namespace muqsit\asynciterator\util;

use Closure;
use pocketmine\timings\TimingsHandler;

final class EmptyTimedClosure{

	/** @var TimingsHandler */
	private $timings;

	/**
	 * @var Closure
	 *
	 * @phpstan-var Closure() : void
	 */
	public $closure;

	/**
	 * @param TimingsHandler $timings
	 * @param Closure $closure
	 *
	 * @phpstan-param Closure() : void $closure
	 */
	public function __construct(TimingsHandler $timings, Closure $closure){
		$this->timings = $timings;
		$this->closure = $closure;
	}

	public function call() : void{
		$this->timings->startTiming();
		($this->closure)();
		$this->timings->stopTiming();
	}
}