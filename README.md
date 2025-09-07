# Quickly

[![Unittest](https://github.com/Idrinth/quickly/actions/workflows/php-unittest.yml/badge.svg)](https://github.com/Idrinth/quickly/actions/workflows/php-unittest.yml)

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

### Compiled Configuration

About twice as fast as even the generated configuration, this is your best option in most cases.

Have a look at [.quickly/entrypoints.php](.quickly/entrypoints.php) for configuring entry points without having to add any Attributes.

```php
<?php

namespace Idrinth\Quickly\Built\DependendyInjection;

use Exception;
use Idrinth\Quickly\DependencyInjection\FallbackFailed;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private readonly array $defined;
    private readonly array $environments;
    private array $built = [];
    public function __construct(array $environments, private readonly ContainerInterface $fallbackContainer)
    {
        foreach (array (
) as $variableName => $environment) {
            if (isset($environments[$environment])) {
                $this->environments["Environment:$variableName"] = $environments[$environment];
            }
        }
        $this->defined = [
            'Idrinth\Quickly\CommandLineOutput'=>true,
'Idrinth\Quickly\CommandLineOutputs\Colorless'=>true,
'Idrinth\Quickly\Commands\Build'=>true,
'Idrinth\Quickly\Commands\Help'=>true,
'Idrinth\Quickly\Commands\Validate'=>true,
        ];
    }

    public function get(string $id): string|object
    {
        if (isset($this->built[$id])) {
            return $this->built[$id];
        }
        return $this->built[$id] = match ($id) {
            'Idrinth\Quickly\CommandLineOutput'=>$this->get('Idrinth\Quickly\CommandLineOutputs\Colorless'),
'Idrinth\Quickly\CommandLineOutputs\Colorless'=>new \Idrinth\Quickly\CommandLineOutputs\Colorless(),
'Idrinth\Quickly\Commands\Build'=>new \Idrinth\Quickly\Commands\Build($this->get('Idrinth\Quickly\CommandLineOutputs\Colorless')),
'Idrinth\Quickly\Commands\Help'=>new \Idrinth\Quickly\Commands\Help($this->get('Idrinth\Quickly\CommandLineOutputs\Colorless')),
'Idrinth\Quickly\Commands\Validate'=>new \Idrinth\Quickly\Commands\Validate($this->get('Idrinth\Quickly\CommandLineOutputs\Colorless')),
            default => $this->fallBackOn($id),
        };
    }
    private function fallBackOn(string $id): object
    {
        try {
            return $this->fallbackContainer->get($id);
        } catch (Exception $e) {
            throw new FallbackFailed("Couldn't fall back on {$id}", previous: $e);
        }
    }

    public function has(string $id): bool
    {
        return isset($this->defined[$id]) || isset($this->environments[$id]) || $this->fallbackContainer->has($id);
    }
}
```

## Environment Variable Mapping

Environment variables are automatically converted to camelCase for injection:

- `DATABASE_URL` → `databaseUrl`
- `API_KEY` → `apiKey` 
- `REDIS_HOST` → `redisHost`

## Error Handling

Quickly provides comprehensive exception handling with PSR-11 compliant exceptions for different error scenarios. All exceptions implement either `Psr\Container\ContainerExceptionInterface` or `Psr\Container\NotFoundExceptionInterface`.

### Configuration & Setup Errors

- **`InvalidClassName`** - Invalid or empty class name provided in configuration
    - Extends: `InvalidArgumentException`
    - Implements: `Psr\Container\ContainerExceptionInterface`

- **`InvalidDependency`** - Invalid dependency definition in constructor arguments
    - Extends: `InvalidArgumentException`
    - Implements: `Psr\Container\ContainerExceptionInterface`

- **`DependencyTypeUnknown`** - Unknown dependency definition type encountered
    - Extends: `InvalidArgumentException`
    - Implements: `Psr\Container\ContainerExceptionInterface`

### Resolution & Runtime Errors

- **`DependencyNotFound`** - Service not registered in container
    - Extends: `OutOfBoundsException`
    - Implements: `Psr\Container\NotFoundExceptionInterface`

- **`DependencyUnbuildable`** - Cannot construct service due to runtime issues
    - Extends: `UnexpectedValueException`
    - Implements: `Psr\Container\ContainerExceptionInterface`

- **`CircularDependency`** - Circular dependency detected during resolution
    - Extends: `DependencyUnbuildable`
    - Implements: `Psr\Container\ContainerExceptionInterface` (inherited)

- **`FallbackFailed`** - Fallback container failed to provide dependency
    - Extends: `DependencyUnbuildable`
    - Implements: `Psr\Container\ContainerExceptionInterface` (inherited)

### Build-time Errors

- **`DependencyUnresolvable`** - Dependency cannot be resolved at build time
    - Extends: `DependencyUnbuildable`
    - Implements: `Psr\Container\ContainerExceptionInterface` (inherited)

### Factory-specific Errors

- **`NoImplementationFound`** - Factory cannot resolve implementation for given parameters
    - Extends: `UnexpectedValueException`
    - Implements: `Psr\Container\NotFoundExceptionInterface`

### Exception Hierarchy

```
InvalidArgumentException
├── InvalidClassName
├── InvalidDependency
└── DependencyTypeUnknown

OutOfBoundsException
└── DependencyNotFound

UnexpectedValueException
├── DependencyUnbuildable
│   ├── CircularDependency
│   ├── DependencyUnresolvable
│   └── FallbackFailed
└── NoImplementationFound
```

All exceptions are PSR-11 compliant and provide detailed error messages to help with debugging dependency injection issues.

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
