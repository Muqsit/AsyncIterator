<?php

declare(strict_types=1);

namespace muqsit\asynciterator;

use muqsit\asynciterator\handler\SimpleAsyncForeachHandler;
use pocketmine\scheduler\Task;

/**
 * @phpstan-template TKey
 * @phpstan-template TValue
 */
final class AsyncForeachTask extends Task{

	/**
	 * @param SimpleAsyncForeachHandler $async_foreach_handler
	 *
	 * @phpstan-param SimpleAsyncForeachHandler<TKey, TValue> $async_foreach_handler
	 */
	public function __construct(
		private SimpleAsyncForeachHandler $async_foreach_handler
	){}

	public function onRun() : void{
		if(!$this->async_foreach_handler->handle()){
			$this->async_foreach_handler->doCompletion();
			$task_handler = $this->getHandler();
			if($task_handler !== null){
				$task_handler->cancel();
			}
		}
	}
}