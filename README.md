# AsyncIterator

`AsyncIterator` simplifies writing asynchronous iteration tasks, such as for-eaching an iterator.

```php
/** @var Plugin $plugin */
$handler = new AsyncIterator($plugin->getScheduler());
```

<hr>

`AsyncIterator::forEach` traverses forward over an `Iterator` type and notifies handlers in the order of insertion.
Handlers can be added to a `forEach` task by feeding a `Closure` to `AsyncIterator::forEach()::as()`, having the signature `function(TKey $key, TValue $value) : AsyncForeachResult`.

```php
$handler->forEach(new ArrayIterator([1, 2]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo "First ", $value;
	return AsyncForeachResult::CONTINUE();
})
->as(function(int $key, int $value) : AsyncForeachResult{
	echo "Second ", $value;
	return AsyncForeachResult::CONTINUE();
});
```
```
First 1
Second 1
First 2
Second 2
```

<hr>

By default, `AsyncIterator::forEach` traverses over 10 entries each tick. This can be changed by overriding the default parameter values of the method.
```php
$entries_per_tick = 4;
$sleep_time = 1; // in ticks
AsyncIterator::forEach(new InfiniteIterator(new ArrayIterator([1, 2, 3])), $entries_per_tick, $sleep_time)
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value, ", ";
	return AsyncForeachResult::CONTINUE();
});
```
```
# First Tick:
1, 2, 3, 1

# Second Tick:
2, 3, 1, 2

...
```

<hr>

Completion listeners are triggered when a foreach task successfully completes. This is determined by the return value of `Iterator::valid()` (i.e., `$completed = !Iterator::valid()`) of the iterator that is passed to `AsyncIterator::forEach`.

```php
$handler->forEach(new ArrayIterator([1, 2, 3]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value;
	return AsyncForeachResult::CONTINUE();
})
->onCompletion(function() : void{ echo "Completed"; })
```

<hr>

Handlers have the ability to either continue, interrupt or cancel the traversal by returning either `AsyncForeachResult::CONTINUE()`, `AsyncForeachResult::INTERRUPT()` or `AsyncForeachResult::CANCEL()` respectively.
When interrupted, a `forEach` task will not traverse the iterator anymore and notify interrupt listeners immediately.
However, when cancelled, a `forEach` task will notify no listeners and immediately dispose the task away.

```php
$handler->forEach(new ArrayIterator([1, 2, 3]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value;
	return $value === 3 ? AsyncForeachResult::INTERRUPT() : AsyncForeachResult::CONTINUE();
})
->onCompletion(function() : void{ echo "Completed"; })
->onInterruption(function() : void{ echo "Interrupted"; });
```
```
1
2
Interrupted
```

```php

$handler->forEach(new ArrayIterator([1, 2, 3]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value;
	return AsyncForeachResult::CONTINUE();
})
->onCompletion(function() : void{ echo "Completed"; })
->onInterruption(function() : void{ echo "Interrupted"; });
```
```
1
2
3
Completed
```

<hr>

Interruption of a `forEach` task can also occur externally (outside the handler) by calling the `interrupt()` method on the return value of `forEach`.

```php

$foreach_task = $handler->forEach(new ArrayIterator([1, 2, 3]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value;
	return AsyncForeachResult::CONTINUE();
})
->onCompletion(function() : void{ echo "Completed"; })
->onInterruption(function() : void{ echo "Interrupted"; });

TaskScheduler::scheduleDelayedTask(function() use($foreach_task) : void{
	$foreach_task->interrupt();
}, ticks: 2);
```
```
1
2
Interrupted
```

<hr>
A `forEach` task may be cancelled by calling the `cancel()` method on the return value of `forEach`, causing no interrupt or completion listeners to be notified.

```php

$foreach_task = $handler->forEach(new ArrayIterator([1, 2, 3]))
->as(function(int $key, int $value) : AsyncForeachResult{
	echo $value;
	return AsyncForeachResult::CONTINUE();
})
->onCompletion(function() : void{ echo "Completed"; })
->onInterruption(function() : void{ echo "Interrupted"; });

TaskScheduler::scheduleDelayedTask(function() use($foreach_task) : void{
	$foreach_task->cancel();
}, ticks: 2);
```
```
1
2
```

<hr>
