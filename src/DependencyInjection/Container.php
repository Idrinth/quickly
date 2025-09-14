<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\Environment;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Exception;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

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
    /**
     * @var Array<string, string[]|string>
     */
    private array $nameMap;
    /**
     * @var Array<string, Array<string, Definition>>
     */
    private array $overwrites;
    private bool $useReflection;
    /**
     * @var Array<string, ReflectionClass>
     */
    private array $reflectionClassMap = [];

    /**
     * @param Array<string, string> $environments
     * @param Array<string, Array<string, Definition>> $overwrites
     * @param ContainerInterface $fallbackContainer
     * @param Array<string, Definition> $constructors
     * @param Array<string, string> $factories
     * @param Array<string, string> $classAliases
     */
    public function __construct(array $environments, array $overwrites, private readonly ContainerInterface $fallbackContainer, array $constructors = [], array $factories = [], array $classAliases = [])
    {
        $this->definitions = [];
        $this->environments = [];
        foreach ($environments as $environment => $value) {
            $key = lcfirst(str_replace('_', '', ucwords(strtolower($environment), '_')));
            $this->environments['Environment:' . $key] = $value;
            $this->definitions['Environment:' . $key] = new Environment($key);
        }
        $this->useReflection = isset($environments['DI_USE_REFLECTION']) && strtolower($environments['DI_USE_REFLECTION']) === 'true';
        $disableValidation = isset($environments['DI_USE_CONFIG_VALIDATION']) && strtolower($environments['DI_USE_CONFIG_VALIDATION']) === 'false';
        $this->objects = [];
        $this->constructors = [];
        foreach ($constructors as $className => $dependencies) {
            if (!$disableValidation) {
                if (!is_string($className) || empty($className)) {
                    throw new InvalidClassName('Class name must be a string');
                }
                foreach ($dependencies as $dependency) {
                    if (!($dependency instanceof Definition)) {
                        throw new InvalidDependency("Received an invalid dependency in the constructor argument of $className");
                    }
                }
            }
            $this->nameMap['ClassObject:'.$className] = ['ClassObject:'.$className, ['ClassObject', $className]];
            $this->nameMap[$className] = ['ClassObject:'.$className, ['ClassObject', $className]];
            $this->constructors['ClassObject:'.$className] = $dependencies;
            $this->definitions['ClassObject:' . $className] = new ClassObject($className);
        }
        $this->factories = $this->mapKeys('Factory', $factories, $disableValidation);
        foreach ($this->factories as $className) {
            $this->definitions['ClassObject:' . $className] = new ClassObject($className);
        }
        $this->classAliases = $this->mapKeys('Alias', $classAliases, $disableValidation);
        foreach ($classAliases as $className) {
            $this->definitions['ClassObject:' . $className] = new ClassObject($className);
        }
        $this->overwrites = [];
        foreach ($overwrites as $className => $parameters) {
            if (!$disableValidation) {
                if (!is_string($className) || empty($className)) {
                    throw new InvalidClassName('Class name must be a string');
                }
            }
            if (count($parameters) > 0) {
                $overwrites[$className] = [];
                foreach ($parameters as $parameterName => $parameter) {
                    if (!$disableValidation) {
                        if (!is_string($parameterName) || empty($parameterName)) {
                            throw new InvalidPropertyName("Class name pf $className($parameter) must be a string");
                        }
                        if (!($parameter instanceof Definition)) {
                            throw new InvalidDependency("Received an invalid dependency in the constructor argument of $className");
                        }
                    }
                    $this->overwrites[$className][$parameterName] = $parameter;
                }
            }
        }
    }
    private function mapKeys(string $prefix, array $list, bool $disableValidation): array
    {
        $newList = [];
        foreach ($list as $className => $targetName) {
            if (!$disableValidation) {
                if (!is_string($className) || empty($className)) {
                    throw new InvalidClassName('Class name must be a string');
                }
                if (!is_string($targetName) || empty($targetName)) {
                    throw new InvalidClassName('Target name must be a string');
                }
            }
            $newList[$prefix.':'.$className] = $targetName;
            $this->nameMap[$className] = ['ClassObject:'.$targetName, ['ClassObject', $targetName]];
            $this->nameMap[$prefix.':'.$className] = ['ClassObject:'.$targetName, ['ClassObject', $targetName]];
        }
        return $newList;
    }

    private function toDefinition(string $id): Definition
    {
        if (!isset($this->nameMap[$id])) {
            if (!str_contains($id, ':')) {
                $this->nameMap[$id] = ["ClassObject:$id", ['ClassObject', $id]];
                return $this->definitions['ClassObject:' . $id] ?? new ClassObject($id);
            }
            $this->nameMap[$id] = [$id, explode(':', $id)];
        }
        if (isset($this->definitions[$this->nameMap[$id][0]])) {
            return $this->definitions[$this->nameMap[$id][0]];
        }
        return $this->definitions[$id] = match ($this->nameMap[$id][1][0]) {
            'Factory' => new Definitions\Factory($this->nameMap[$id][1][1], $this->nameMap[$id][1][2], $this->nameMap[$id][1][3], $this->nameMap[$id][1][4]),
            'ClassObject' => isset($this->classAliases[$this->nameMap[$id][1][1]]) ? new ClassObject($this->classAliases[$this->nameMap[$id][1][1]]) : new ClassObject($this->nameMap[$id][1][1]),
            default => throw new DependencyTypeUnknown("Dependency definition type {$this->nameMap[$id][1][0]} is unknown"),
        };
    }

    /**
     * @throws ReflectionException
     */
    private function toReflectionClass(string $id): ReflectionClass
    {
        if (isset($this->reflectionClassMap[$id])) {
            return $this->reflectionClassMap[$id];
        }
        return $this->reflectionClassMap[$id] = new ReflectionClass($id);
    }
    /**
     * @param Definition $definition
     * @param string[] $previous previous initialization attempts to detect loops
     * @return object
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveWithReflection(Definition $definition, string ...$previous): object
    {
        $class = $definition->getId();
        $reflection = $this->toReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return $this->objects["$definition"] = new $class();
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            if (isset($this->overwrites[$class][$parameter->getName()])) {
                $arguments[] = $this->resolve($this->overwrites[$class][$parameter->getName()], ...$previous);
                continue;
            }
            $attributes = $parameter->getAttributes(ResolveWithFactory::class);
            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                $arguments[] = $this->resolve(new ClassObject(
                    $this
                        ->resolve(new ClassObject($attribute->class), ...$previous)
                        ->pickImplementation($parameter->getName(), $attribute->key, $class),
                ));
                continue 2;
            }
            $type = $parameter->getType();
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $subtype) {
                    if ($subtype instanceof ReflectionNamedType) {
                        try {
                            $arguments[] = $this->resolve($this->toDefinition($subtype->getName()), ...$previous);
                            continue 2;
                        } catch (DependencyUnresolvable $e) {
                            //can be ignored here, this is just trying out all options
                        }
                    }
                }
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($parameter->allowsNull()) {
                    $arguments[] = null;
                    continue;
                }
                throw new DependencyUnresolvable("UnionType of {$parameter->getName()} is not supported");
            }
            if ($type instanceof ReflectionIntersectionType) {
                foreach ($type->getTypes() as $subtype) {
                    if ($subtype instanceof ReflectionNamedType) {
                        try {
                            $resolved = $this->resolve($this->toDefinition($subtype->getName()), ...$previous);
                            foreach ($type->getTypes() as $subtype1) {
                                if (!($subtype1 instanceof ReflectionNamedType) || !$this->toReflectionClass($subtype1->getName())->isInstance($resolved)) {
                                    continue 2;
                                }
                            }
                            continue 2;
                        } catch (DependencyUnresolvable $e) {
                            //can be ignored here, this is just trying out all options
                        }
                    } else {
                        break;
                    }
                }
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($parameter->allowsNull()) {
                    $arguments[] = null;
                    continue;
                }
                throw new DependencyUnresolvable("IntersectionType of {$parameter->getName()} is not supported");
            }
            if ($type->isBuiltin()) {
                if ($type->getName() === 'string' && str_starts_with($parameter->getName(), 'env') && strtoupper($parameter->getName()[3]) === $parameter->getName()[3]) {
                    $arguments[] = $this->resolve(new Environment(lcfirst(substr($parameter->getName(), 3))), ...[...$previous, "$definition"]);
                    continue;
                }
                $attributes = $parameter->getAttributes(EnvironmentInject::class);
                foreach ($attributes as $attribute) {
                    $attribute = $attribute->newInstance();
                    $key = lcfirst(str_replace('_', '', ucwords(strtolower($attribute->environmentName), '_')));
                    $arguments[] = $this->resolve(new Environment($key), ...[...$previous, "$definition"]);
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
                throw new DependencyUnbuildable("$definition needs unsupported type {$type->getName()}");
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
            }
            $arguments[] = $this->resolve(new ClassObject($type->getName()), ...[...$previous, "$definition"]);
        }
        try {
            return $this->objects["$definition"] = new $class(...$arguments);
        } catch (Exception $e) {
            try {
                return $this->fallbackContainer->get($class);
            } catch (Exception $e2) {
                throw new FallbackFailed("$class couldn't be build: $e2", previous: $e);
            }
        }
    }
    private function resolve(Definition $definition, string ...$previous): object|string
    {
        if (isset($this->objects["$definition"])) {
            return $this->objects["$definition"];
        }
        if (isset($this->classAliases['Alias:'.$definition->getId()])) {
            return $this->objects["$definition"] = $this->resolve($this->toDefinition($this->classAliases['Alias:'.$definition->getId()]), ...$previous);
        }
        if ($definition->getType() === DefinitionTypes::Environment) {
            return $this->environments["$definition"]
                ?? throw new DependencyNotFound("Environment {$definition->getId()} could not be found");
        }
        if (in_array("$definition", $previous, true)) {
            throw new CircularDependency(implode('->', $previous).'->'.$definition);
        }
        if ($definition->getType() === DefinitionTypes::Factory) {
            /**
             * @var Definitions\Factory $definition
             * @var Factory $factory
             */
            $factory = $this->resolve(new ClassObject($definition->getId()), ...$previous);
            if (!($factory instanceof Factory)) {
                throw new DependencyNotFound("Factory {$definition->getId()} is not a factory");
            }
            $implementation = $factory->pickImplementation($definition->getParameter(), $definition->getKey(), $definition->getForClass());
            return $this->objects["$definition"] = $this->resolve(new ClassObject($implementation, $definition->isLazy()), ...[...$previous, "$definition"]);
        }
        $class = $definition->getId();

        if (!isset($this->constructors["$definition"])) {
            if ($this->useReflection && class_exists($class)) {
                return $this->resolveWithReflection($definition, ...[...$previous, "$definition"]);
            }
            throw new DependencyNotFound("$class is unknown");
        }
        try {
            if ($definition->isLazy()) {
                return $this->objects["$definition"] = $this->toReflectionClass($class)->newLazyGhost(fn() => new $class(
                    ...array_map(
                        fn(Definition $definition) => $this->resolve($definition, ...$previous),
                        $this->constructors["$definition"]
                    )
                ));
            }
            return $this->objects["$definition"] = new $class(
                ...array_map(
                    fn(Definition $definition) => $this->resolve($definition, ...$previous),
                    $this->constructors["$definition"]
                )
            );
        } catch (Exception $e) {
            try {
                return $this->fallbackContainer->get($class);
            } catch (Exception $e2) {
                throw new FallbackFailed("$class couldn't be build: $e2", previous: $e);
            }
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
        if (isset($this->objects["ClassObject:$id"])) {
            return $this->objects["ClassObject:$id"];
        }
        return $this->resolve($this->toDefinition($id));
    }

    public function has(string $id): bool
    {
        $parts = explode(':', $id);
        if (count($parts) === 1) {
            array_unshift($parts, 'ClassObject');
        }
        if (isset($this->objects[implode(':', $parts)])) {
            return true;
        }
        if ($parts[0] === 'Environment') {
            return isset($this->environments[$id]);
        }
        if ($parts[0] === 'Factory') {
            return isset($this->factories['Factory:'.$parts[1]]);
        }
        return isset($this->constructors['ClassObject:'.$parts[1]])
            || isset($this->classAliases['Alias:'.$parts[1]]);
    }
}
