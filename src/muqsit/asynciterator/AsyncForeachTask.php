<?php

declare(strict_types=1);

namespace muqsit\asynciterator;

use muqsit\asynciterator\handler\AsyncForeachHandler;
use pocketmine\scheduler\Task;

/**
 * @template TKey
 * @template TValue
 */
final class AsyncForeachTask extends Task{

	/**
	 * @param AsyncForeachHandler<TKey, TValue> $async_foreach_handler
	 */
	public function __construct(
		readonly private AsyncForeachHandler $async_foreach_handler
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