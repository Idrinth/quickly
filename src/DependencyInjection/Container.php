<?php

namespace Idrinth\Quickly\DependencyInjection;

use Psr\Container\ContainerInterface;
use Exception;
use ReflectionClass;
use ReflectionNamedType;

final class Container implements ContainerInterface
{
    /**
     * @var Array<string, string>
     */
    private array $environments;
    /**
     * @var Array<string, object>
     */
    private array $objects;
    /**
     * @var Array<string, string[]>
     */
    private array $constructors;
    /**
     * @var Array<string, string[]>
     */
    private array $factories;
    private bool $useReflection;

    public function __construct(array $environments, array $constructors = [], array $factories  = [])
    {
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
                if (!is_string($dependency) || empty($dependency)) {
                    throw new InvalidDependency("Received an invalid dependency in the constructor argument of $className");
                }
                if (!str_starts_with($dependency, 'Environment:') && !str_starts_with($dependency, 'ClassObject:') && !str_starts_with($dependency, 'Factory:')) {
                    throw new InvalidDependency("Received an invalid dependency '$dependency' in the constructor argument of $className");
                }
            }
            $this->constructors['ClassObject:'.$className] = $dependencies;
        }
        $this->factories = [];
        foreach ($factories as $className) {
            if (!is_string($className) || empty($className)) {
                throw new InvalidClassName('Class name must be a string');
            }
            $this->factories['Factory:'.$className] = $className;
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
        if (str_starts_with($id, 'Environment:')) {
            if (isset($this->environments[$id])) {
                return $this->environments[$id];
            }
            throw new DependencyNotFound("Environment {$id} could not be found");
        }
        if (str_starts_with($id, 'Factory:')) {
            if (isset($this->objects[$id])) {
                return $this->objects[$id];
            }
            $parts = explode(':', $id);
            /**
             * @var Factory $factory
             */
            $factory = $this->get('ClassObject:'.$parts[1]);
            if (!($factory instanceof Factory)) {
                throw new DependencyNotFound("Factory {$parts[1]} is not a factory");
            }
            $implementation = $factory->pickImplementation(parameter: $parts[2], key: $parts[3], forClass: $parts[4]);
            return $this->objects[$id] = $this->get('ClassObject:'.$implementation);
        }
        if (!str_starts_with($id, 'ClassObject:')) {
            throw new DependencyUnbuildable("Type of {$id} is not supported");
        }
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }
        $class = substr($id, 12);
        if (!isset($this->constructors[$id])) {
            if ($this->useReflection && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $constructor = $reflection->getConstructor();
                if (!$constructor) {
                    return $this->objects[$id] = new $class();
                }
                $arguments = [];
                foreach ($constructor->getParameters() as $parameter) {
                    $attributes = $parameter->getAttributes(ResolveWithFactory::class);
                    foreach ($attributes as $attribute) {
                        $attribute = $attribute->newInstance();
                        if (isset($this->factories[$attribute->class])) {
                            $resolved = $this
                                ->get('ClassObject:'.$this->factories[$attribute->class])
                                ->pickImplementation($parameter->getName(), $attribute->key, $class);
                            if ($resolved) {
                                return $resolved;
                            }
                        }
                    }
                    $type = $parameter->getType();
                    if (!$type instanceof ReflectionNamedType) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $arguments[] = $parameter->getDefaultValue();
                            continue;
                        }
                        if ($parameter->isOptional()) {
                            break;
                        }
                        throw new DependencyUnbuildable("Type of {$type} is not supported");
                    }
                    if ($type->isBuiltin()) {
                        if ($type->getName() === 'string' && str_starts_with($parameter->getName(), 'env')) {
                            $arguments[] = $this->get('Environment:' . lcfirst(substr($parameter->getName(), 3)));
                            continue;
                        }
                        $attributes = $parameter->getAttributes(EnvironmentInject::class);
                        foreach ($attributes as $attribute) {
                            $attribute = $attribute->newInstance();
                            $key = lcfirst(str_replace('_', '', ucwords(strtolower($attribute->environmentName), '_')));
                            if (isset($this->environments['Environment:' . $key])) {
                                $arguments[] = $this->get('Environment:'.$key);
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
                        throw new DependencyUnbuildable("$id needs unsupported type {$type->getName()}");
                    }
                    if (!isset($this->constructors['ClassObject:' . $type->getName()])) {
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
                return $this->objects[$id] = new $class(...$arguments);
            }
            throw new DependencyNotFound("$id is unknown");
        }
        try {
            return $this->objects[$id] = new $class(...array_map([$this, 'get'], $this->constructors[$id]));
        } catch (Exception $e) {
            throw new DependencyUnbuildable("$id can't be built.", previous: $e);
        }
    }

    public function has(string $id): bool
    {
        return isset($this->objects[$id]) || isset($this->constructors[$id])
            || isset($this->environments[$id]) || isset($this->factories[$id]);
    }
}
