<?php

declare(strict_types=1);

namespace muqsit\asynciterator\handler;

use Closure;

/**
 * @phpstan-template TKey
 * @phpstan-template TValue
 */
interface AsyncForeachHandler{

	/**
	 * Called on each iterated entry. Accepts two parameters,
	 * the first being the key, and the second being the value,
	 * and returns a boolean that corresponds to continuing
	 * iteration if true, and interrupting if false.
	 *
	 * @param Closure $callback
	 * @return AsyncForeachHandler
	 *
	 * @phpstan-param Closure(TKey, TValue) : bool $callback
	 * @phpstan-return AsyncForeachHandler<TKey, TValue>
	 */
	public function as(Closure $callback) : self;

	/**
	 * Stops the foreach task, scheduling interrupt listeners
	 * to get triggered on the next task run.
	 */
	public function interrupt() : void;

	/**
	 * Stops the foreach task.
	 *
	 * WARNING: Cancelling a foreach task will trigger NEITHER
	 * {@see AsyncForeachHandler::onInterruption()} callbacks, nor
	 * {@see AsyncForeachHandler::onCompletion()} callbacks.
	 */
	public function cancel() : void;

	/**
	 * Called after the foreach task is completed successfully.
	 *
	 * @param Closure $callback
	 * @return AsyncForeachHandler
	 *
	 * @phpstan-param Closure() : void $callback
	 * @phpstan-return AsyncForeachHandler<TKey, TValue>
	 */
	public function onCompletion(Closure $callback) : self;

	/**
	 * Called after the foreach task is interrupted.
	 *
	 * @param Closure $callback
	 * @return AsyncForeachHandler
	 *
	 * @phpstan-param Closure() : void $callback
	 * @phpstan-return AsyncForeachHandler<TKey, TValue>
	 */
	public function onInterruption(Closure $callback) : self;

	/**
	 * Called after the foreach task is interrupted.
	 *
	 * @param Closure $callback
	 * @return AsyncForeachHandler
	 *
	 * @phpstan-param Closure() : void $callback
	 * @phpstan-return AsyncForeachHandler<TKey, TValue>
	 */
	public function onCompletionOrInterruption(Closure $callback) : self;
}