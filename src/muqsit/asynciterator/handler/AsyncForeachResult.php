<?php

declare(strict_types=1);

namespace muqsit\asynciterator\handler;

abstract class AsyncForeachResult{

	public static function CONTINUE() : self{
		static $instance = null;
		return $instance ??= new class() extends AsyncForeachResult{
			public function handle(AsyncForeachHandler $handler) : bool{
				return true;
			}
		};
	}

	public static function INTERRUPT() : self{
		static $instance = null;
		return $instance ??= new class() extends AsyncForeachResult{
			public function handle(AsyncForeachHandler $handler) : bool{
				$handler->interrupt();
				return false;
			}
		};
	}

	public static function CANCEL() : self{
		static $instance = null;
		return $instance ??= new class() extends AsyncForeachResult{
			public function handle(AsyncForeachHandler $handler) : bool{
				$handler->cancel();
				return false;
			}
		};
	}

	private function __construct(){
	}

	/**
	 * @param AsyncForeachHandler<mixed, mixed> $handler
	 * @return bool
	 */
	abstract public function handle(AsyncForeachHandler $handler) : bool;
}