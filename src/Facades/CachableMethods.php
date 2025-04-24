<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods\Facades;

use Fereydooni\CachableMethods\Services\MethodProxy;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the CachableMethods service.
 *
 * @method static mixed call(object $object, string $method, array $parameters = [], array $additionalParams = [])
 * @method static bool flushByTags(array $tags)
 *
 * @see \Fereydooni\CachableMethods\Services\MethodProxy
 */
class CachableMethods extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cachable-methods';
    }
} 