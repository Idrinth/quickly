<?php

namespace Idrinth\Quickly\Commands\Build;

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
        foreach ($envToInject=[] as $variableName => $environment) {
            if (isset($environments[$environment])) {
                $this->environments["Environment:$variableName"] = $environments[$environment];
            }
        }
        $this->defined = [
            //Definitions,
        ];
    }

    public function get(string $id): string|object
    {
        if (isset($this->built[$id])) {
            return $this->built[$id];
        }
        return $this->built[$id] = match ($id) {
            //Cases,
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
