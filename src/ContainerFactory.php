<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

use Psr\Container\ContainerInterface;

interface ContainerFactory
{
    public function createContainer(): ContainerInterface;
}
