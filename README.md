# Cachable Methods

A Laravel package for method caching using PHP attributes. This package allows you to cache method results by simply adding an attribute to your methods.

## Installation

You can install the package via composer:

```bash
composer require fereydooni/cachable-methods
```

The package will automatically register the service provider.

If you want to customize the configuration, you can publish it:

```bash
php artisan vendor:publish --tag="cachable-methods-config"
```

This will publish the configuration file to `config/cachable-methods.php`.

## Usage

### Basic Usage

Simply add the `Cacheable` attribute to any method you want to cache:

```php
use Fereydooni\CachableMethods\Attributes\Cacheable;

class UserService
{
    #[Cacheable(ttl: 3600)]
    public function getUser(int $id): User
    {
        return User::findOrFail($id);
    }
}
```

Then, to call the method with caching enabled, use the `CachableMethods` facade:

```php
use Fereydooni\CachableMethods\Facades\CachableMethods;

$userService = new UserService();
$user = CachableMethods::call($userService, 'getUser', [1]);
```

### Customizing Cache Keys

You can customize the cache key by providing a `key` parameter:

```php
#[Cacheable(ttl: 3600, key: 'user_{0}')]
public function getUser(int $id): User
{
    return User::findOrFail($id);
}
```

The placeholder `{0}` will be replaced with the first parameter passed to the method.

### Using Cache Tags

You can tag your cached results for easier invalidation:

```php
#[Cacheable(ttl: 3600, tags: ['users', 'profiles'])]
public function getUserProfile(int $id): array
{
    return User::with('profile')->findOrFail($id)->toArray();
}
```

### Skipping Cache

You can skip the cache for a specific method call:

```php
$freshUser = CachableMethods::call($userService, 'getUser', [1], ['skip_cache' => true]);
```

### Invalidating Cache

To invalidate cache by tags:

```php
use Fereydooni\CachableMethods\Facades\CachableMethods;

// Flush all caches with the 'users' tag
CachableMethods::flushByTags(['users']);
```

## Configuration

The package can be configured in `config/cachable-methods.php`:

```php
return [
    // Enable or disable caching globally
    'enabled' => env('CACHABLE_METHODS_ENABLED', true),
    
    // Default TTL in seconds
    'default_ttl' => env('CACHABLE_METHODS_TTL', 3600),
    
    // Cache store to use (null for default)
    'store' => env('CACHABLE_METHODS_STORE', null),
    
    // Parameter name to skip cache
    'skip_cache_flag' => 'skip_cache',
    
    // Cache key prefix
    'key_prefix' => 'cachable_',
];
```

## Package Structure

```
cachable-methods/
├── config/
│   └── cachable-methods.php       # Configuration file
├── src/
│   ├── Attributes/
│   │   └── Cacheable.php         # The main attribute class
│   ├── Contracts/
│   │   └── CacheHandlerInterface.php  # Interface for the cache handler
│   ├── Facades/
│   │   └── CachableMethods.php    # Facade for easy access
│   ├── Services/
│   │   ├── CacheHandler.php      # Main caching logic
│   │   └── MethodProxy.php       # Method call interceptor
│   └── CachableMethodsServiceProvider.php  # Service provider
├── tests/
│   ├── Feature/                  # Feature tests
│   ├── Unit/                     # Unit tests
│   ├── Pest.php                  # Pest configuration
│   └── TestCase.php              # Base test case
├── .github/
│   └── workflows/
│       └── tests.yml             # CI workflow
├── composer.json                 # Composer configuration
├── LICENSE                      # MIT License
├── phpstan.neon                  # PHPStan configuration
└── README.md                     # This file
```

## Error Handling

The package gracefully handles cache store failures. If the cache is unavailable, the method will execute normally (uncached) and errors will be logged.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information. 