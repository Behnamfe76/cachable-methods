<?php

namespace Fereydooni\CachableMethods\Tests\Unit;

use Fereydooni\CachableMethods\Attributes\Cacheable;
use Fereydooni\CachableMethods\Facades\CachableMethods;
use Fereydooni\CachableMethods\Tests\TestCase;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class CachableMethodsTest extends TestCase
{
    /**
     * Test class with cacheable methods for testing.
     */
    protected TestUserService $service;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create an instance of our test service
        $this->service = new TestUserService();
        
        // Clear the cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_caches_method_results()
    {
        // First call - should not be from cache
        $firstResult = CachableMethods::call($this->service, 'getUser', [1]);
        
        // Confirm the method was actually called
        $this->assertEquals(1, $this->service->methodCallCount);
        $this->assertEquals('User 1', $firstResult);
        
        // Second call - should be from cache
        $secondResult = CachableMethods::call($this->service, 'getUser', [1]);
        
        // Method call count should not increase
        $this->assertEquals(1, $this->service->methodCallCount);
        $this->assertEquals('User 1', $secondResult);
    }

    /** @test */
    public function it_respects_custom_cache_key()
    {
        // First call with custom key
        $firstResult = CachableMethods::call($this->service, 'getUserWithCustomKey', [1]);
        
        $this->assertEquals(1, $this->service->customKeyMethodCallCount);
        $this->assertEquals('User 1 (custom key)', $firstResult);
        
        // Second call with same key
        $secondResult = CachableMethods::call($this->service, 'getUserWithCustomKey', [1]);
        
        // Call count should not increase
        $this->assertEquals(1, $this->service->customKeyMethodCallCount);
        $this->assertEquals('User 1 (custom key)', $secondResult);
        
        // Check if value is in cache with correct key
        $this->assertTrue(Cache::has('cachable_custom_user_key_1'));
    }

    /** @test */
    public function it_skips_cache_when_requested()
    {
        // First call
        $firstResult = CachableMethods::call($this->service, 'getUser', [1]);
        $this->assertEquals(1, $this->service->methodCallCount);
        
        // Second call with skip_cache flag
        $secondResult = CachableMethods::call($this->service, 'getUser', [1], ['skip_cache' => true]);
        
        // Call count should increase since we skipped cache
        $this->assertEquals(2, $this->service->methodCallCount);
    }

    /** @test */
    public function it_can_flush_cache_by_tags()
    {
        // Cache a result with tags
        CachableMethods::call($this->service, 'getUserWithTags', [1]);
        $this->assertEquals(1, $this->service->taggedMethodCallCount);
        
        // Second call should be from cache
        CachableMethods::call($this->service, 'getUserWithTags', [1]);
        $this->assertEquals(1, $this->service->taggedMethodCallCount);
        
        // Flush the cache by tags
        CachableMethods::flushByTags(['users']);
        
        // Next call should miss the cache
        CachableMethods::call($this->service, 'getUserWithTags', [1]);
        $this->assertEquals(2, $this->service->taggedMethodCallCount);
    }
}

/**
 * Test service class with cacheable methods.
 */
class TestUserService
{
    public int $methodCallCount = 0;
    public int $customKeyMethodCallCount = 0;
    public int $taggedMethodCallCount = 0;

    #[Cacheable(ttl: 60)]
    public function getUser(int $id): string
    {
        $this->methodCallCount++;
        return "User {$id}";
    }

    #[Cacheable(ttl: 60, key: 'custom_user_key_{0}')]
    public function getUserWithCustomKey(int $id): string
    {
        $this->customKeyMethodCallCount++;
        return "User {$id} (custom key)";
    }

    #[Cacheable(ttl: 60, tags: ['users'])]
    public function getUserWithTags(int $id): string
    {
        $this->taggedMethodCallCount++;
        return "User {$id} (with tags)";
    }
} 