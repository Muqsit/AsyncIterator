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
	 * @var SimpleAsyncForeachHandler
	 *
	 * @phpstan-var SimpleAsyncForeachHandler<TKey, TValue>
	 */
	private $async_foreach_handler;

	/**
	 * @param SimpleAsyncForeachHandler $async_foreach_handler
	 *
	 * @phpstan-param SimpleAsyncForeachHandler<TKey, TValue> $async_foreach_handler
	 */
	public function __construct(SimpleAsyncForeachHandler $async_foreach_handler){
		$this->async_foreach_handler = $async_foreach_handler;
	}

	public function onRun() : void{
		if(!$this->async_foreach_handler->handle()){
			$this->async_foreach_handler->doCancel();
			$task_handler = $this->getHandler();
			if($task_handler !== null){
				$task_handler->cancel();
			}
		}
	}
}