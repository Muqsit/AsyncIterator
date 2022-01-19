<?php

declare(strict_types=1);

namespace muqsit\asynciterator\util;

use Closure;
use pocketmine\timings\TimingsHandler;

final class EmptyTimedClosure{

	/**
	 * @param TimingsHandler $timings
	 * @param Closure() : void $closure
	 */
	public function __construct(
		private TimingsHandler $timings,
		public Closure $closure
	){}

	public function call() : void{
		$this->timings->startTiming();
		($this->closure)();
		$this->timings->stopTiming();
	}
}