<?php

declare(strict_types=1);

namespace muqsit\asynciterator\handler;

use Closure;
use Iterator;

/**
 * @phpstan-template TKey
 * @phpstan-template TValue
 * @phpstan-implements AsyncForeachHandler<TKey, TValue>
 */
final class SimpleAsyncForeachHandler implements AsyncForeachHandler{

	private const COMPLETION_CALLBACKS = 0;
	private const INTERRUPTION_CALLBACKS = 1;
	private const EMPTY_CALLBACKS = 2;

	/**
	 * @var Iterator
	 * @phpstan-var Iterator<TKey, TValue>
	 */
	private $iterable;

	/** @var int */
	private $entries_per_tick;

	/** @var Closure[] */
	private $callbacks = [];

	/** @var int */
	private $finalization_type = self::COMPLETION_CALLBACKS;

	/** @var Closure[][] */
	private $finalization_callbacks = [
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
	public function __construct(Iterator $iterable, int $entries_per_tick){
		$this->iterable = $iterable;
		$this->entries_per_tick = $entries_per_tick;
		$iterable->rewind();
	}

	public function interrupt() : void{
		$this->callbacks = [static function($key, $value) : bool{ return false; }];
		$this->finalization_type = self::INTERRUPTION_CALLBACKS;
	}

	public function cancel() : void{
		$this->callbacks = [static function($key, $value) : bool{ return false; }];
		$this->finalization_type = self::EMPTY_CALLBACKS;
	}

	public function handle() : bool{
		$per_run = $this->entries_per_tick;
		while($this->iterable->valid()){
			$key = $this->iterable->key();
			$value = $this->iterable->current();
			foreach($this->callbacks as $callback){
				if(!$callback($key, $value)){
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

	public function doCancel() : void{
		foreach($this->finalization_callbacks[$this->finalization_type] as $callback){
			$callback();
		}
	}

	public function as(Closure $callback) : AsyncForeachHandler{
		$this->callbacks[spl_object_id($callback)] = $callback;
		return $this;
	}

	public function onCompletion(Closure $callback) : AsyncForeachHandler{
		$this->finalization_callbacks[self::COMPLETION_CALLBACKS][spl_object_id($callback)] = $callback;
		return $this;
	}

	public function onInterruption(Closure $callback) : AsyncForeachHandler{
		$this->finalization_callbacks[self::INTERRUPTION_CALLBACKS][spl_object_id($callback)] = $callback;
		return $this;
	}

	public function onCompletionOrInterruption(Closure $callback) : AsyncForeachHandler{
		return $this->onCompletion($callback)
			->onInterruption($callback);
	}
}