<?php

declare(strict_types=1);

namespace Fereydooni\CachableMethods;

/**
 * This file documents the Laravel dependencies required by the package.
 * 
 * The package depends on the following Laravel components:
 * 
 * - illuminate/support: For ServiceProvider and Facades
 * - illuminate/contracts: For CacheFactory and Repository interfaces
 * - illuminate/cache: For caching functionality
 * 
 * These dependencies are automatically installed when you install Laravel,
 * but they are listed explicitly in the composer.json file to ensure
 * they are available when using this package outside of a full Laravel installation.
 */

if (!function_exists('Fereydooni\CachableMethods\config_path')) {
    /**
     * Get the configuration path.
     *
     * This is a helper function to prevent errors in the service provider
     * when the Laravel config_path() function is not available.
     *
     * @param  string  $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        if (function_exists('config_path')) {
            return \config_path($path);
        }

        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

// Provide global helper to access application instance
if (!function_exists('Fereydooni\CachableMethods\app')) {
    /**
     * Get the application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    function app()
    {
        return \app();
    }
} 