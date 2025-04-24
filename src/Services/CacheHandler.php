<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods\Services;

use Fereydooni\CachableMethods\Attributes\Cacheable;
use Fereydooni\CachableMethods\Contracts\CacheHandlerInterface;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use Throwable;

/**
 * Handles the caching logic for methods with the Cacheable attribute.
 */
class CacheHandler implements CacheHandlerInterface
{
    /**
     * The cache repository instance.
     *
     * @var Repository
     */
    protected Repository $cache;

    /**
     * Create a new CacheHandler instance.
     *
     * @param CacheFactory $cacheFactory The cache factory instance
     * @param array<string, mixed> $config Package configuration
     */
    public function __construct(
        protected CacheFactory $cacheFactory,
        protected array $config
    ) {
        $store = $this->config['store'] ?? null;
        $this->cache = $store
            ? $this->cacheFactory->store($store)
            : $this->cacheFactory->store();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        object $object,
        string $method,
        array $parameters,
        ReflectionMethod $reflectionMethod,
        Cacheable $attribute,
        array $additionalParams = []
    ): mixed {
        // Skip caching if it's disabled globally or specifically for this call
        if (
            !($this->config['enabled'] ?? true) ||
            ($additionalParams[$this->config['skip_cache_flag'] ?? 'skip_cache'] ?? false)
        ) {
            return $reflectionMethod->invokeArgs($object, $parameters);
        }

        try {
            $cacheKey = $this->generateCacheKey($object, $method, $parameters, $attribute);
            $ttl = $attribute->ttl ?? $this->config['default_ttl'] ?? 3600;
            
            /** @var Repository|TaggedCache $cache */
            $cache = empty($attribute->tags) ? $this->cache : $this->cache->tags($attribute->tags);
            
            // Check if the value is already in the cache
            if ($cache->has($cacheKey)) {
                return $cache->get($cacheKey);
            }
            
            // Execute the method and store its result in the cache
            $result = $reflectionMethod->invokeArgs($object, $parameters);
            
            // Store result in cache
            $cache->put($cacheKey, $result, $ttl);
            
            return $result;
        } catch (Throwable $e) {
            // Log the error but let the method execute without caching
            Log::error("CachableMethods error: {$e->getMessage()}", [
                'exception' => $e,
                'class' => get_class($object),
                'method' => $method,
            ]);
            
            return $reflectionMethod->invokeArgs($object, $parameters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateCacheKey(
        object $object,
        string $method,
        array $parameters,
        Cacheable $attribute
    ): string {
        // Use custom key if provided
        if ($attribute->key !== null) {
            return $this->config['key_prefix'] . $attribute->key;
        }
        
        // Generate key based on class, method, and parameters
        $className = get_class($object);
        $keyParts = [$className, $method];
        
        // Add serialized parameters to the key parts
        foreach ($parameters as $param) {
            if (is_object($param)) {
                // For objects, use class name and object hash
                $keyParts[] = get_class($param) . '_' . spl_object_hash($param);
            } elseif (is_array($param)) {
                // For arrays, use md5 hash of serialized content
                try {
                    $keyParts[] = 'array_' . md5(serialize($param));
                } catch (Throwable) {
                    // If serialization fails, use a fallback
                    $keyParts[] = 'array_' . count($param);
                }
            } elseif (is_scalar($param) || is_null($param)) {
                // For scalars and null, use string representation
                $keyParts[] = (string) $param;
            } else {
                // For other types, use type name
                $keyParts[] = gettype($param);
            }
        }
        
        // Create final key
        return $this->config['key_prefix'] . md5(implode('_', $keyParts));
    }

    /**
     * {@inheritdoc}
     */
    public function flushByTags(array $tags): bool
    {
        try {
            if (method_exists($this->cache, 'tags')) {
                $this->cache->tags($tags)->flush();
                return true;
            }
            
            // Log warning if the current cache store doesn't support tags
            Log::warning('CachableMethods: Current cache driver does not support tags.');
            return false;
        } catch (Throwable $e) {
            Log::error("CachableMethods error while flushing by tags: {$e->getMessage()}", [
                'exception' => $e,
                'tags' => $tags,
            ]);
            return false;
        }
    }
} 