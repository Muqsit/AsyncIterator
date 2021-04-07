<?php

declare(strict_types=1);

namespace muqsit\asynciterator;

use muqsit\asynciterator\handler\AsyncForeachHandler;
use muqsit\asynciterator\handler\SimpleAsyncForeachHandler;
use Iterator;
use pocketmine\scheduler\TaskScheduler;

class AsyncIterator{

	private TaskScheduler $scheduler;

	public function __construct(TaskScheduler $scheduler){
		$this->scheduler = $scheduler;
	}

	/**
	 * @param Iterator $iterable
	 * @param int $entries_per_tick
	 * @param int $sleep_time
	 * @return AsyncForeachHandler
	 *
	 * @phpstan-template TKey
	 * @phpstan-template TValue
	 * @phpstan-param Iterator<TKey, TValue> $iterable
	 * @phpstan-return AsyncForeachHandler<TKey, TValue>
	 */
	public function forEach(Iterator $iterable, int $entries_per_tick = 10, int $sleep_time = 1) : AsyncForeachHandler{
		$handler = new SimpleAsyncForeachHandler($iterable, $entries_per_tick);
		$task_handler = $this->scheduler->scheduleDelayedRepeatingTask(new AsyncForeachTask($handler), 1, $sleep_time);
		$handler->init("Plugin: {$task_handler->getOwnerName()} Event: AsyncIterator");
		return $handler;
	}
}