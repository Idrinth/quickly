<?php

namespace Idrinth\Quickly\DependencyInjection;

use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\Environment;
use Psr\Container\ContainerInterface;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

final class Container implements ContainerInterface
{
    /**
     * @var Array<string, Definition>
     */
    private array $definitions;
    /**
     * @var Array<string, string>
     */
    private array $environments;
    /**
     * @var Array<string, object>
     */
    private array $objects;
    /**
     * @var Array<string, Definition[]>
     */
    private array $constructors;
    /**
     * @var Array<string, string>
     */
    private array $factories;
    /**
     * @var Array<string, string>
     */
    private array $classAliases;
    private bool $useReflection;

    /**
     * @param Array<string, string> $environments
     * @param Array<string, Definition> $constructors
     * @param Array<string, string> $factories
     * @param Array<string, string> $classAliases
     */
    public function __construct(array $environments, array $constructors = [], array $factories = [], array $classAliases = [])
    {
        $this->definitions = [];
        $this->environments = [];
        foreach ($environments as $environment => $value) {
            $key = lcfirst(str_replace('_', '', ucwords(strtolower($environment), '_')));
            $this->environments['Environment:' . $key] = $value;
        }
        $this->useReflection = isset($environments['DI_USE_REFLECTION']) && strtolower($environments['DI_USE_REFLECTION']) === 'true';
        $this->objects = [];
        $this->constructors = [];
        foreach ($constructors as $className => $dependencies) {
            if (!is_string($className) || empty($className)) {
                throw new InvalidClassName('Class name must be a string');
            }
            foreach ($dependencies as $dependency) {
                if (!($dependency instanceof Definition)) {
                    throw new InvalidDependency("Received an invalid dependency in the constructor argument of $className");
                }
            }
            $this->constructors['ClassObject:'.$className] = $dependencies;
        }
        $this->factories = $this->mapKeys('Factory', $factories);
        $this->classAliases = $this->mapKeys('Alias', $classAliases);
    }
    private function mapKeys(string $prefix, array $list): array
    {
        $newList = [];
        foreach ($list as $className => $targetName) {
            if (!is_string($className) || empty($className)) {
                throw new InvalidClassName('Class name must be a string');
            }
            if (!is_string($targetName) || empty($targetName)) {
                throw new InvalidClassName('Target name must be a string');
            }
            $newList[$prefix.':'.$className] = $targetName;
        }
        return $newList;
    }

    private function toDefinition(string $id): Definition
    {
        if (isset($this->definitions[$id])) {
            return $this->definitions[$id];
        }
        if (isset($this->classAliases[$id])) {
            return $this->definitions[$id] = $this->toDefinition('ClassObject:'.$this->classAliases[$id]);
        }
        $parts = explode(':', $id);
        return $this->definitions[$id] = match ($parts[0]) {
            'Environment' => new Environment($parts[1]),
            'Factory' => new Definitions\Factory($parts[1], $parts[2], $parts[3], $parts[4]),
            'ClassObject' => new ClassObject($parts[1]),
            default => throw new DependencyTypeUnknown("Dependency definition type {$parts[0]} is unknown"),
        };
    }

    /**
     * @param Definition $definition
     * @return object
     * @throws ReflectionException
     */
    private function resolveWithReflection(Definition $definition): object
    {
        $class = $definition->getId();
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return $this->objects["$definition"] = new $class();
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(ResolveWithFactory::class);
            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                $arguments[] = $this->resolve(new ClassObject(
                    $this
                        ->resolve(new ClassObject($attribute->class))
                        ->pickImplementation($parameter->getName(), $attribute->key, $class),
                ));
                continue 2;
            }
            $type = $parameter->getType();
            if (!($type instanceof ReflectionNamedType)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($parameter->isOptional()) {
                    break;
                }
                throw new DependencyUnresolvable("Type of {$parameter->getName()} is not supported");
            }
            if ($type->isBuiltin()) {
                if ($type->getName() === 'string' && str_starts_with($parameter->getName(), 'env')) {
                    $arguments[] = $this->resolve(new Environment(lcfirst(substr($parameter->getName(), 3))));
                    continue;
                }
                $attributes = $parameter->getAttributes(EnvironmentInject::class);
                foreach ($attributes as $attribute) {
                    $attribute = $attribute->newInstance();
                    $key = lcfirst(str_replace('_', '', ucwords(strtolower($attribute->environmentName), '_')));
                    if (isset($this->environments['Environment:' . $key])) {
                        $arguments[] = $this->resolve(new Environment($key));
                        continue 2;
                    }
                }
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($type->allowsNull()) {
                    $arguments[] = null;
                    continue;
                }
                if ($parameter->isOptional()) {
                    break;
                }
                throw new DependencyUnbuildable("$definition needs unsupported type {$type->getName()}");
            }
            if (isset($this->classAliases['Alias:' . $type->getName()])) {
                $arguments[] = $this->get('ClassObject:' . $type->getName());
                continue;
            }
            if (!isset($this->constructors['ClassObject:' . $type->getName()])) {
                if (isset($this->classAliases['Alias:' . $type->getName()])) {
                    $arguments[] = $this->get('Alias:' . $type->getName());
                    continue;
                }
                foreach ($parameter->getAttributes(ResolveWithFactory::class) as $attribute) {
                    $attribute = $attribute->newInstance();
                    $arguments[] = $this->resolve(new Definitions\Factory($attribute->class, $parameter->getName(), $attribute->key, $class));
                    continue 2;
                }
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($type->allowsNull()) {
                    $arguments[] = null;
                    continue;
                }
                if ($parameter->isOptional()) {
                    break;
                }
            }
            $arguments[] = $this->get('ClassObject:' . $type->getName());
        }
        try {
            return $this->objects["$definition"] = new $class(...$arguments);
        } catch (Exception $e) {
            throw new DependencyUnbuildable("$class couldn't be build", previous: $e);
        }
    }
    private function resolve(Definition $definition): object|string
    {
        if ($definition->getType() === DefinitionTypes::Environment) {
            return $this->environments["$definition"]
                ?? throw new DependencyNotFound("Environment {$definition->getId()} could not be found");
        }
        if (isset($this->objects["$definition"])) {
            return $this->objects["$definition"];
        }
        if ($definition->getType() === DefinitionTypes::Factory) {
            /**
             * @var Definitions\Factory $definition
             * @var Factory $factory
             */
            $factory = $this->resolve(new ClassObject($definition->getId()));
            if (!($factory instanceof Factory)) {
                throw new DependencyNotFound("Factory {$definition->getId()} is not a factory");
            }
            $implementation = $factory->pickImplementation($definition->getParameter(), $definition->getKey(), $definition->getForClass());
            return $this->objects["$definition"] = $this->get('ClassObject:'.$implementation);
        }
        $class = $definition->getId();

        if (!isset($this->constructors["$definition"])) {
            if ($this->useReflection && class_exists($class)) {
                try {
                    return $this->resolveWithReflection($definition);
                } catch (ReflectionException $e) {
                    throw new DependencyUnbuildable("Reflection on definition {$definition->getId()} failed");
                }
            }
            throw new DependencyNotFound("$class is unknown");
        }
        try {
            return $this->objects["$definition"] = new $class(...array_map([$this, 'resolve'], $this->constructors["$definition"]));
        } catch (Exception $e) {
            throw new DependencyUnbuildable("$class can't be built.", previous: $e);
        }
    }
    /**
     * @param string $id
     * @return object|string
     * @throws DependencyUnbuildable if building the requirement fails
     * @throws DependencyNotFound if the entry can not be found
     */
    public function get(string $id): object|string
    {
        return $this->resolve($this->toDefinition($id));
    }

    public function has(string $id): bool
    {
        return isset($this->objects[$id]) || isset($this->constructors[$id])
            || isset($this->environments[$id]) || isset($this->factories[$id])
            || isset($this->classAliases[$id]);
    }
}
