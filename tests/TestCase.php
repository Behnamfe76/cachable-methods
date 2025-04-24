<?php

namespace Fereydooni\CachableMethods\Tests;

use Fereydooni\CachableMethods\CachableMethodsServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for all tests.
 */
abstract class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CachableMethodsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set cache driver to array for testing
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cachable-methods.enabled', true);
        $app['config']->set('cachable-methods.default_ttl', 60);
    }
} 