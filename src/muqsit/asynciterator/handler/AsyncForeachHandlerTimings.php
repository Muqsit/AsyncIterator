<?php

declare(strict_types=1);

namespace muqsit\asynciterator\handler;

use Closure;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\Utils;

final class AsyncForeachHandlerTimings{

	/** @var TimingsHandler[] */
	private static array $on_traversal_handlers = [];

	/** @var TimingsHandler[] */
	private static array $on_completion_handlers = [];

	/** @var TimingsHandler[] */
	private static array $on_interruption_handlers = [];

	public static function getTraverserTimings(string $parent, Closure $callback) : TimingsHandler{
		return self::$on_traversal_handlers[$name = "{$parent}::as " . Utils::getNiceClosureName($callback)] ??= new TimingsHandler($name);
	}

	public static function getOnCompletionTimings(string $parent, Closure $callback) : TimingsHandler{
		return self::$on_completion_handlers[$name = "{$parent}::onCompletion " . Utils::getNiceClosureName($callback)] ??= new TimingsHandler($name);
	}

	public static function getOnInterruptionTimings(string $parent, Closure $callback) : TimingsHandler{
		return self::$on_interruption_handlers[$name = "{$parent}::onInterruption " . Utils::getNiceClosureName($callback)] ??= new TimingsHandler($name);
	}
}