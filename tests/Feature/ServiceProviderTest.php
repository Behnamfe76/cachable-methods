<?php

namespace Fereydooni\CachableMethods\Tests\Feature;

use Fereydooni\CachableMethods\Contracts\CacheHandlerInterface;
use Fereydooni\CachableMethods\Facades\CachableMethods;
use Fereydooni\CachableMethods\Services\MethodProxy;
use Fereydooni\CachableMethods\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_cache_handler_interface()
    {
        $this->assertTrue($this->app->bound(CacheHandlerInterface::class));
    }

    /** @test */
    public function it_registers_cache_attribute_facade()
    {
        $this->assertInstanceOf(MethodProxy::class, CachableMethods::getFacadeRoot());
    }

    /** @test */
    public function it_loads_configuration()
    {
        $this->assertEquals(true, config('cachable-methods.enabled'));
        $this->assertEquals(60, config('cachable-methods.default_ttl'));
        $this->assertEquals('skip_cache', config('cachable-methods.skip_cache_flag'));
    }
} 