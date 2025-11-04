<?php

namespace Exowpee\LaravelFlow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Exowpee\LaravelFlow\Context start(array $data = [])
 * @method static \Exowpee\LaravelFlow\Context|null current()
 * @method static \Exowpee\LaravelFlow\Context emit(string $eventName)
 * @method static \Exowpee\LaravelFlow\Context stop()
 *
 * @see \Exowpee\LaravelFlow\FlowManager
 */
class Flow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flow';
    }
}