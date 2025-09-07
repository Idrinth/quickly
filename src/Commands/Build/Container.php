<?php

namespace Idrinth\Quickly\Commands\Build;

use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private array $defined;
    public function __construct(private readonly ContainerInterface $fallbackContainer)
    {
        $this->defined = [
            //Definitions,
        ];
    }

    public function get(string $id): string|object
    {
        if (isset($this->defined[$id]) && $this->defined[$id] !== true) {
            return $this->defined[$id];
        }
        return $this->defined[$id] = match ($id) {
            //Cases,
            default => $this->fallbackContainer->get($id),
        };
    }

    public function has(string $id): bool
    {
        return isset($this->defined[$id]) || $this->fallbackContainer->has($id);
    }
}
