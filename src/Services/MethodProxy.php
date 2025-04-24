<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods\Services;

use Fereydooni\CachableMethods\Attributes\Cacheable;
use Fereydooni\CachableMethods\Contracts\CacheHandlerInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * Proxies method calls to handle caching based on attributes.
 */
class MethodProxy
{
    /**
     * Create a new MethodProxy instance.
     */
    public function __construct(
        protected CacheHandlerInterface $cacheHandler
    ) {
    }

    /**
     * Handle method calls and apply caching if the method has a Cacheable attribute.
     *
     * @param object $object The instance to call the method on
     * @param string $method Method name
     * @param array<mixed> $parameters Method parameters
     * @param array<string, mixed> $additionalParams Additional parameters (like skip_cache)
     * @return mixed The method result, potentially from cache
     */
    public function call(
        object $object,
        string $method,
        array $parameters = [],
        array $additionalParams = []
    ): mixed {
        $reflectionClass = new ReflectionClass($object);
        
        // Check if the method exists
        if (!$reflectionClass->hasMethod($method)) {
            throw new \BadMethodCallException(
                sprintf('Method %s::%s does not exist.', get_class($object), $method)
            );
        }
        
        $reflectionMethod = $reflectionClass->getMethod($method);
        
        // Look for Cacheable attribute
        $attributes = $reflectionMethod->getAttributes(Cacheable::class);
        
        if (empty($attributes)) {
            // No cacheable attribute found, just call the method normally
            return $reflectionMethod->invokeArgs($object, $parameters);
        }
        
        // Get the Cacheable attribute instance
        $attribute = $attributes[0]->newInstance();
        
        // Handle the method call with caching
        return $this->cacheHandler->handle(
            $object,
            $method,
            $parameters,
            $reflectionMethod,
            $attribute,
            $additionalParams
        );
    }
    
    /**
     * Flush the cache by tags.
     *
     * @param array<string> $tags Tags to flush
     * @return bool True if successfully flushed
     */
    public function flushByTags(array $tags): bool
    {
        return $this->cacheHandler->flushByTags($tags);
    }
} 