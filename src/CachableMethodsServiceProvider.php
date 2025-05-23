<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods;

use Fereydooni\CachableMethods\Contracts\CacheHandlerInterface;
use Fereydooni\CachableMethods\Services\CacheHandler;
use Fereydooni\CachableMethods\Services\MethodProxy;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the CachableMethods package.
 * 
 * @property \Illuminate\Foundation\Application $app
 */
class CachableMethodsServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Register the config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cachable-methods.php', 'cachable-methods'
        );

        // Register the cache handler
        $this->app->singleton(CacheHandlerInterface::class, function ($app) {
            return new CacheHandler(
                $app->make(CacheFactory::class),
                $app['config']['cachable-methods']
            );
        });

        // Register the method proxy
        $this->app->singleton('cachable-methods', function ($app) {
            return new MethodProxy(
                $app->make(CacheHandlerInterface::class)
            );
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish the config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/cachable-methods.php' => package_config_path('cachable-methods.php'),
            ], 'cachable-methods-config');
        }
    }
} 