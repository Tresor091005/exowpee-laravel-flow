<?php

namespace Exowpee\LaravelFlow\Facades;

use Exowpee\LaravelFlow\Context;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Context start(array $data = [])
 * @method static Context|null current()
 * @method static Context hook(string $eventName)
 * @method static Context stop()
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