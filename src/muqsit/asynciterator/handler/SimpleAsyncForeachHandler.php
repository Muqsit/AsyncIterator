<?php

declare(strict_types=1);

namespace muqsit\asynciterator\handler;

use Closure;
use Iterator;
use muqsit\asynciterator\util\EmptyTimedClosure;
use muqsit\asynciterator\util\KeyValueTimedClosure;

/**
 * @phpstan-template TKey
 * @phpstan-template TValue
 * @phpstan-implements AsyncForeachHandler<TKey, TValue>
 */
final class SimpleAsyncForeachHandler implements AsyncForeachHandler{

	private const COMPLETION_CALLBACKS = 0;
	private const INTERRUPTION_CALLBACKS = 1;
	private const EMPTY_CALLBACKS = 2;

	private string $timings_parent_name;

	/**
	 * @var KeyValueTimedClosure[]
	 *
	 * @phpstan-var array<KeyValueTimedClosure<TKey, TValue, AsyncForeachResult>>
	 */
	private array $callbacks = [];

	private int $finalization_type = self::COMPLETION_CALLBACKS;

	/**
	 * @var EmptyTimedClosure[][]
	 *
	 * @phpstan-var array<int, array<EmptyTimedClosure>>
	 */
	private array $finalization_callbacks = [
		self::COMPLETION_CALLBACKS => [],
		self::INTERRUPTION_CALLBACKS => [],
		self::EMPTY_CALLBACKS => []
	];

	/**
	 * @param Iterator $iterable
	 * @param int $entries_per_tick
	 *
	 * @phpstan-param Iterator<TKey, TValue> $iterable
	 */
	public function __construct(
		private Iterator $iterable,
		private int $entries_per_tick
	){
		$iterable->rewind();
	}

	public function init(string $timings_parent_name) : void{
		$this->timings_parent_name = $timings_parent_name;
	}

	public function interrupt() : void{
		$this->cancelNext();
		$this->finalization_type = self::INTERRUPTION_CALLBACKS;
	}

	public function cancel() : void{
		$this->cancelNext();
		$this->finalization_type = self::EMPTY_CALLBACKS;
	}

	private function cancelNext() : void{
		$this->callbacks = [];
		$this->as(static function($key, $value) : AsyncForeachResult{ return AsyncForeachResult::CANCEL(); });
	}

	public function handle() : bool{
		$per_run = $this->entries_per_tick;
		while($this->iterable->valid()){
			/** @phpstan-var TKey $key */
			$key = $this->iterable->key();

			/** @phpstan-var TValue $value */
			$value = $this->iterable->current();

			foreach($this->callbacks as $callback){
				if(!$callback->call($key, $value)->handle($this)){
					return false;
				}
			}

			$this->iterable->next();
			if(--$per_run === 0){
				return true;
			}
		}

		return false;
	}

	public function doCompletion() : void{
		foreach($this->finalization_callbacks[$this->finalization_type] as $callback){
			$callback->call();
		}
	}

	public function as(Closure $callback) : AsyncForeachHandler{
		$this->callbacks[spl_object_id($callback)] = new KeyValueTimedClosure(AsyncForeachHandlerTimings::getTraverserTimings($this->timings_parent_name, $callback), $callback);
		return $this;
	}

	public function onCompletion(Closure $callback) : AsyncForeachHandler{
		$this->finalization_callbacks[self::COMPLETION_CALLBACKS][spl_object_id($callback)] = new EmptyTimedClosure(AsyncForeachHandlerTimings::getOnCompletionTimings($this->timings_parent_name, $callback), $callback);
		return $this;
	}

	public function onInterruption(Closure $callback) : AsyncForeachHandler{
		$this->finalization_callbacks[self::INTERRUPTION_CALLBACKS][spl_object_id($callback)] = new EmptyTimedClosure(AsyncForeachHandlerTimings::getOnInterruptionTimings($this->timings_parent_name, $callback), $callback);
		return $this;
	}

	public function onCompletionOrInterruption(Closure $callback) : AsyncForeachHandler{
		return $this->onCompletion($callback)
			->onInterruption($callback);
	}
}
