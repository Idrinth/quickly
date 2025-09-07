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
use Idrinth\Quickly\DependencyInjection\DependencyUnbuildable;
use Idrinth\Quickly\DependencyInjection\DependencyUnresolvable;
use Idrinth\Quickly\DependencyInjection\EnvironmentInject;
use Idrinth\Quickly\DependencyInjection\Definitions\Factory;
use Idrinth\Quickly\DependencyInjection\LazyInitialization;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

final class Build implements Command
{
    private array $data;
    public function __construct(private CommandLineOutput $output)
    {
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
        $interfaces = [];
        foreach ($classes as $class => $path) {
            if ($class instanceof Throwable) {
                continue;
            }
            try {
                $reflection = new ReflectionClass($class);
                if (!$reflection->isAbstract() && !$reflection->isInterface() && !$reflection->isTrait()) {
                    foreach ($reflection->getInterfaces() as $interface) {
                        $interfaces[$interface->getName()] = $interfaces[$interface->getName()] ?? [];
                        $interfaces[$interface->getName()][] = $class;
                    }
                    $parent = $reflection->getParentClass();
                    while ($parent) {
                        foreach ($parent->getInterfaces() as $interface) {
                            $interfaces[$interface->getName()] = $interfaces[$interface->getName()] ?? [];
                            $interfaces[$interface->getName()][] = $class;
                        }
                        if ($parent->isAbstract()) {
                            $interfaces[$parent->getName()] = $interfaces[$parent->getName()] ?? [];
                            $interfaces[$parent->getName()][] = $class;
                        }
                        $parent = $parent->getParentClass();
                    }
                }
            } catch (Exception $e) {
                $this->output->errorLine($e->getMessage());
            }
        }
        foreach ($interfaces as $interface => $implementations) {
            $implementations = array_unique($implementations);
            if (count($implementations) === 1) {
                $this->data['classAliases'][$interface] = $implementations[0];
            }
        }
        foreach ($classes as $class => $path) {
            if ($class instanceof Throwable) {
                continue;
            }
            try {
                //$reflection = new ReflectionClass($class);
                //foreach($reflection->getAttributes(DependencyInjectionEntrypoint::class) as $attribute) {
                $this->buildDependencyDefinition($class);
                //}
            } catch (Exception $e) {
                $this->output->errorLine($e->getMessage());
            }
        }
        if (!is_dir(__DIR__ . '/../../.quickly')) {
            mkdir(__DIR__ . '/../../.quickly', 0755, true);
        }
        file_put_contents(__DIR__ . '/../../.quickly/generated.php', '<?php return '.var_export($this->data, true).';');
        $definitions = [];
        $cases = [];
        foreach ($this->data['classAliases'] as $alias => $class) {
            $cases[] = "'$alias'=>\$this->get('$class')";
            $definitions[] = "'$alias'=>true";
        }
        foreach ($this->data['constructors'] as $class => $constructor) {
            $params = [];
            foreach ($constructor as $param) {
                $params[] = match(get_class($param)) {
                    Environment::class => "\$this->get('Environment:{$param->getId()}')",
                    StaticValue::class => var_export($param->getValue(), true),
                    Factory::class => "\$this->get(\$this->get('Factory:{$param->getId()}')->pickImplementation('{$param->getParameter()}','{$param->getKey()}','{$param->getForClass()}'))",
                    default => "\$this->get('{$param->getId()}')",
                };
            }
            $cases[] = "'$class'=>new \\$class(".implode(',', $params).")";
            $definitions[] = "'$class'=>true";
        }
        file_put_contents(
            __DIR__ . '/../../.quickly/Container.php',
            str_replace(
                [
                    '//Cases',
                    '//Definitions',
                    'namespace Idrinth\\Quickly\\Commands\\Build;'
                ],
                [
                    implode(",\n", $cases),
                    implode(",\n", $definitions),
                    'namespace Idrinth\\Quickly\\Built\\DependendyInjection;'
                ],
                file_get_contents(__DIR__ . '/Build/Container.php')
            )
        );
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
        if ($reflection->isEnum()) {
            throw new DependencyUnbuildable("$class is an enum, skipping.");
        }
        if ($reflection->isTrait()) {
            throw new DependencyUnbuildable("$class is a trait, skipping.");
        }
        if ($reflection->isInterface() || $reflection->isAbstract()) {
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
        if (!$constructor->isPublic()) {
            if (isset($this->data['classAliases'][$class])) {
                return $this->buildDependencyDefinition($this->data['classAliases'][$class], ...$previous);
            }
            throw new DependencyUnbuildable("$class has no public constructor attached.");
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter instanceof ReflectionParameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType) {
                    if ($type->isBuiltin()) {
                        if ($parameter->isPassedByReference()) {
                            throw new DependencyUnbuildable("$class has constructor parameter $parameter->name that is a simple value passed by reference.");
                        }
                        if (str_starts_with($parameter->getName(), 'env') && strtoupper($parameter->getName()[3]) === $parameter->getName()[3] && $type->getName() === 'string') {
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
                        if ($parameter->allowsNull() || $type->allowsNull()) {
                            $this->data['staticValues'][serialize(null)] = $this->data['staticValues'][serialize(null)] ?? new StaticValue(null);
                            $arguments[] = $this->data['staticValues'][serialize(null)];
                            continue;
                        }
                        throw new DependencyUnresolvable("Can't find a value to use for {$parameter->getName()} of {$class}.");
                    }
                    if ($type->getName() instanceof Throwable) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $default = $parameter->getDefaultValue();
                            $this->data['staticValues'][serialize($default)] = $this->data['staticValues'][serialize($default)] ?? new StaticValue($default);
                            $arguments[] = $this->data['staticValues'][serialize($default)];
                            continue;
                        }
                        if ($parameter->allowsNull() || $type->allowsNull()) {
                            $this->data['staticValues'][serialize(null)] = $this->data['staticValues'][serialize(null)] ?? new StaticValue(null);
                            $arguments[] = $this->data['staticValues'][serialize(null)];
                            continue;
                        }
                        throw new DependencyUnresolvable("Can't resolve Throwable at build time for {$parameter->getName()} of {$class}.");
                    }
                    if (new ReflectionClass($type->getName())->isEnum()) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $default = $parameter->getDefaultValue();
                            $this->data['staticValues'][serialize($default)] = $this->data['staticValues'][serialize($default)] ?? new StaticValue($default);
                            $arguments[] = $this->data['staticValues'][serialize($default)];
                            continue;
                        }
                        if ($parameter->allowsNull() || $type->allowsNull()) {
                            $this->data['staticValues'][serialize(null)] = $this->data['staticValues'][serialize(null)] ?? new StaticValue(null);
                            $arguments[] = $this->data['staticValues'][serialize(null)];
                            continue;
                        }
                        throw new DependencyUnresolvable("Can't resolve Enum at build time for {$parameter->getName()} of {$class}.");
                    }
                    $arguments[] = $this->buildDependencyDefinition($type->getName(), ...[...$previous, $class]);
                    continue;
                }
                throw new DependencyUnresolvable($class);
            }
        }
        $this->data['constructors'][$class] = $arguments;
        return new ClassObject($class, $isLazy);
    }
}
