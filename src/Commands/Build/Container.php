<?php

namespace Idrinth\Quickly\Commands\Build;

use Psr\Container\ContainerInterface;

final readonly class Container implements ContainerInterface
{
    private array $defined;
    public function __construct(private ContainerInterface $fallbackContainer)
    {
        $this->defined = [
            //Definitions,
        ];
    }

    public function get(string $id): string|object
    {
        return match ($id) {
            //Cases,
            default => $this->fallbackContainer->get($id),
        };
    }

    public function has(string $id): bool
    {
        return isset($this->defined[$id]) || $this->fallbackContainer->has($id);
    }
}
