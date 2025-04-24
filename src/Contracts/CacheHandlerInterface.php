<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods\Contracts;

use Fereydooni\CachableMethods\Attributes\Cacheable;
use ReflectionMethod;

/**
 * Interface for cache handlers that store and retrieve cached method results.
 */
interface CacheHandlerInterface
{
    /**
     * Get value from cache or execute the method and cache the result.
     *
     * @param object $object The instance containing the method
     * @param string $method Method name
     * @param array<mixed> $parameters Method parameters
     * @param ReflectionMethod $reflectionMethod Reflection of the method
     * @param Cacheable $attribute Cacheable attribute instance
     * @param array<string, mixed> $additionalParams Additional parameters (like skip_cache)
     * @return mixed The cached or freshly computed result
     */
    public function handle(
        object $object,
        string $method,
        array $parameters,
        ReflectionMethod $reflectionMethod,
        Cacheable $attribute,
        array $additionalParams = []
    ): mixed;
    
    /**
     * Generate a cache key based on method signature and parameters.
     *
     * @param object $object The instance containing the method
     * @param string $method Method name
     * @param array<mixed> $parameters Method parameters 
     * @param Cacheable $attribute Cacheable attribute instance
     * @return string The generated cache key
     */
    public function generateCacheKey(
        object $object,
        string $method,
        array $parameters,
        Cacheable $attribute
    ): string;
    
    /**
     * Flush the cache by tags.
     *
     * @param array<string> $tags Tags to flush
     * @return bool True if successfully flushed
     */
    public function flushByTags(array $tags): bool;
} 