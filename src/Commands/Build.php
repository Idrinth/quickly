<?php declare(strict_types = 1);

namespace Idrinth\Quickly\Commands;

use Exception;
use Idrinth\Quickly\Command;
use Idrinth\Quickly\CommandLineOutput;
use Idrinth\Quickly\Commands\Build\DependencyInjectionEntrypoint;
use Idrinth\Quickly\DependencyInjection\CircularDependency;
use Idrinth\Quickly\DependencyInjection\Definition;
use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\Environment;
use Idrinth\Quickly\DependencyInjection\Definitions\StaticValue;
use Idrinth\Quickly\DependencyInjection\DependencyUnresolvable;
use Idrinth\Quickly\DependencyInjection\EnvironmentInject;
use Idrinth\Quickly\DependencyInjection\LazyInitialization;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

final class Build implements Command
{
    /**
     * @var ReflectionClass[]
     */
    private array $reflectedClasses;
    private array $data = [
        'classAliases' => [],
        'factories' => [],
        'constructors' => [],
    ];
    public function __construct(private CommandLineOutput $output)
    {
        $this->reflectedClasses = [];
    }
    public function run(): int
    {
        $this->data = [
            'classAliases' => [],
            'factories' => [],
            'constructors' => [],
            'staticValues' => [],
        ];
        $classes = require (__DIR__ . '/../../vendor/composer/autoload_classmap.php');
        foreach ($classes as $class => $path) {
            try {
                if (!isset($this->reflectedClasses[$class])) {
                    $reflection = new ReflectionClass($class);
                    foreach($reflection->getAttributes(DependencyInjectionEntrypoint::class) as $attribute) {
                        $this->buildDependencyDefinition($class);
                    }
                }
            } catch (Exception $e) {
                $this->output->errorLine($e->getMessage());
            }
        }
        if (!is_dir(__DIR__ . '/../../.quickly')) {
            mkdir(__DIR__ . '/../../.quickly', 0755, true);
        }
        file_put_contents(__DIR__ . '/../../.quickly/generated.php', '<?php return '.var_export($this->data, true).';');
        return 0;
    }

    /**
     * @param string $class
     * @param string ...$previous
     * @return Definition
     * @throws ReflectionException
     */
    private function buildDependencyDefinition(string $class, string ...$previous): Definition
    {
        if (in_array($class, $previous)) {
            throw new CircularDependency(implode('->', $previous).'->'.$class);
        }
        $this->output->infoLine("Reflecting on $class");
        $reflection = new ReflectionClass($class);
        if ($reflection->isInterface()) {
            if (!isset($this->data['classAliases'][$reflection->getName()])) {
                throw new DependencyUnresolvable("Interface $class has no alias attached.");
            }
            return $this->buildDependencyDefinition($this->data['classAliases'][$reflection->getName()], ...$previous);
        }
        $isLazy = !empty($reflection->getAttributes(LazyInitialization::class));
        if (isset($this->data['constructors'][$class])) {
            return new ClassObject($class, $isLazy);
        }
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            $this->data['constructors'][$class] = [];
            return new ClassObject($class, $isLazy);
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter instanceof ReflectionParameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType) {
                    if ($type->isBuiltin()) {
                        if (str_starts_with($parameter->getName(), 'env') && $type->getName() === 'string') {
                            $key = lcfirst(substr($parameter->getName(), 3));
                            $this->data['environment'][$key] = new Environment($key);
                            $arguments[] = $this->data['environment'][$key];
                            continue;
                        }
                        if ($type->getName() === 'string') {
                            foreach ($parameter->getAttributes(EnvironmentInject::class) as $attribute) {
                                $key = lcfirst(str_replace('_', '', ucwords(strtolower($attribute->newInstance()->environmentName), '_')));
                                $this->data['environment'][$key] = new Environment($key);
                                $arguments[] = $this->data['environment'][$key];
                                continue 2;
                            }
                        }
                        if ($parameter->isDefaultValueAvailable()) {
                            $default = $parameter->getDefaultValue();
                            $this->data['staticValues'][serialize($default)] = $this->data['staticValues'][serialize($default)] ?? new StaticValue($default);
                            $arguments[] = $this->data['staticValues'][serialize($default)];
                            continue;
                        }
                        if ($parameter->allowsNull()) {
                            $this->data['staticValues'][serialize(null)] = $this->data['staticValues'][serialize(null)] ?? new StaticValue(null);
                            $arguments[] = $this->data['staticValues'][serialize(null)];
                            continue;
                        }
                        throw new DependencyUnresolvable("Can't find a value to use for {$parameter->getName()} of {$class}.");
                    }
                    $arguments[] = $this->buildDependencyDefinition($class, ...[...$previous, $class]);
                    continue;
                }
                throw new DependencyUnresolvable($class);
            }
            $this->data['constructors'][$class] = $arguments;
            return new ClassObject($class, $isLazy);
        }
        $this->data['constructors'][$class] = [];
        return new ClassObject($class, $isLazy);
    }
}
