<?php

namespace Idrinth\Quickly;

use Psr\Container\ContainerInterface;

interface ContainerFactory
{
    public function createContainer(): ContainerInterface;
}
