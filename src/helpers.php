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

/**
 * Determine if the given function exists in the global namespace.
 *
 * @param string $name
 * @return bool
 */
function function_exists_in_global(string $name): bool
{
    return function_exists('\\' . $name);
}

/**
 * Get the path to a configuration file.
 *
 * @param  string  $path
 * @return string
 */
function package_config_path(string $path = ''): string
{
    if (function_exists_in_global('config_path')) {
        // Call Laravel's config_path function if available
        $configPath = call_user_func('\\config_path', $path);
        if ($configPath !== null) {
            return $configPath;
        }
    }

    // If Laravel's function isn't available, or returns null,
    // use our own implementation
    $app = function_exists_in_global('app') ? call_user_func('\\app') : null;
    if ($app && method_exists($app, 'basePath')) {
        return $app->basePath('config') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
    
    // Fallback for when we can't determine the path
    return dirname(__DIR__, 4) . '/config' . ($path ? DIRECTORY_SEPARATOR . $path : '');
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