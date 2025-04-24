<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods\Attributes;

use Attribute;

/**
 * Attribute to mark methods for caching.
 *
 * When applied to a method, its return value will be cached based on the
 * provided parameters.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable
{
    /**
     * Create a new Cacheable attribute instance.
     *
     * @param int|null $ttl Time to live in seconds. Uses config default if null.
     * @param string|null $key Custom cache key. Auto-generated from method signature if null.
     * @param array<string> $tags Optional cache tags for grouped invalidation.
     */
    public function __construct(
        public readonly ?int $ttl = null,
        public readonly ?string $key = null,
        public readonly array $tags = [],
    ) {
    }
} 