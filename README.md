# Quickly

A fast dependency injection container for PHP featuring build-time resolution and PSR-11 compliance.

## Features

- ✅ **PSR-11 Compliant** - Fully compatible with PSR-11 Container Interface
- ⚡ **Build-time Resolution** - Pre-compile dependency graphs for maximum performance
- 🔧 **Autowiring** - Automatic dependency resolution using reflection
- 🌍 **Environment Injection** - Direct injection of environment variables
- 🏭 **Factory Pattern Support** - Flexible object creation with custom factories
- 💤 **Lazy Initialization** - Defer object creation until needed
- 🔄 **Circular Dependency Detection** - Prevents infinite dependency loops
- 📋 **Validation Tools** - Command-line tools for configuration validation
- 🔗 **Expandable** -a fallback container to the container handles cases not covered by it

## Requirements

- PHP 8.4 or higher
- PSR Container 2.0.2+

## Installation

```bash
composer require idrinth/quickly
```

## Quick Start

### Basic Usage

```php
use Idrinth\Quickly\EnvironmentFactory;

// Create container factory
$factory = new EnvironmentFactory();
$container = $factory->createContainer();

// Get services
$myService = $container->get(MyService::class);
```

### With Reflection (Development Mode)

Set the `DI_USE_REFLECTION=true` environment variable to enable automatic dependency resolution:

```php
// Your environment
$_ENV['DI_USE_REFLECTION'] = 'true';

// Container will automatically resolve dependencies
$container = $factory->createContainer();
$service = $container->get(SomeService::class);
```

## Dependency Injection Attributes

### Environment Variable Injection

```php
use Idrinth\Quickly\DependencyInjection\EnvironmentInject;

class DatabaseService
{
    public function __construct(
        #[EnvironmentInject('DATABASE_URL')]
        private string $databaseUrl,
        
        #[EnvironmentInject('DB_TIMEOUT')]
        private int $timeout = 30
    ) {}
}
```

### Factory-based Resolution

```php
use Idrinth\Quickly\DependencyInjection\ResolveWithFactory;
use Idrinth\Quickly\DependencyInjection\Factory;

class MyFactory implements Factory
{
    public function pickImplementation(string $parameter, string $key, string $forClass): string
    {
        return ConcreteImplementation::class;
    }
}

class ServiceConsumer
{
    public function __construct(
        #[ResolveWithFactory(MyFactory::class, 'implementation-key')]
        private SomeInterface $service
    ) {}
}
```

### Lazy Initialization

```php
use Idrinth\Quickly\DependencyInjection\LazyInitialization;

#[LazyInitialization]
class ExpensiveService
{
}
```

## Command Line Tools

Quickly provides several CLI commands accessible via `vendor/bin/quickly`:

### Build Configuration (Functional)

```bash
vendor/bin/quickly build [filepath]
```

Creates an optimized production configuration file for maximum performance.

### Validate Configuration (WIP)

```bash
vendor/bin/quickly validate [filepath]
```

Validates your dependency injection configuration for errors.

### Help

```bash
vendor/bin/quickly help
```

Shows available commands and usage information.

## Configuration

### Manual Configuration

```php
use Idrinth\Quickly\DependencyInjection\Container;
use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\Environment;

$container = new Container(
    environments: $_ENV,
    constructors: [
        MyService::class => [
            new ClassObject(Dependency::class),
            new Environment('CONFIG_VALUE')
        ]
    ],
    classAliases: [
        'MyInterface' => 'ConcreteImplementation'
    ]
);
```

### Generated Configuration

For production use, generate optimized configuration:

```php
// .quickly/generated.php (example)
return [
    'constructors' => [
        // Pre-resolved constructor dependencies
    ],
    'factories' => [
        // Factory mappings
    ],
    'classAliases' => [
        // Interface to implementation mappings
    ]
];
```

## Environment Variable Mapping

Environment variables are automatically converted to camelCase for injection:

- `DATABASE_URL` → `databaseUrl`
- `API_KEY` → `apiKey` 
- `REDIS_HOST` → `redisHost`

## Error Handling

Quickly provides specific exceptions for different error scenarios:

- `DependencyNotFound` - Service not registered
- `DependencyUnbuildable` - Cannot construct service
- `CircularDependency` - Circular dependency detected
- `InvalidClassName` - Invalid class name provided
- `NoImplementationFound` - Factory cannot resolve implementation

## Advanced Features

### Circular Dependency Detection

```php
// This will throw CircularDependency exception
class ServiceA
{
    public function __construct(ServiceB $serviceB) {}
}

class ServiceB  
{
    public function __construct(ServiceA $serviceA) {}
}
```

### Singleton Behavior

All services are automatically singletons - the same instance is returned for subsequent requests:

```php
$service1 = $container->get(MyService::class);
$service2 = $container->get(MyService::class);
// $service1 === $service2
```

## Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit directly:

```bash
vendor/bin/phpunit
```

### Benchmarks

[Idrinth/php-dependency-injection-benchmark](https://github.com/Idrinth/php-dependency-injection-benchmark) contains up to date benchmarks with comparisons to competitors.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

**Björn 'Idrinth' Büttner**

---

Built with ❤️ for high-performance PHP applications.
