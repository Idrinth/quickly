<?php

namespace Idrinth\Quickly\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidDependency extends InvalidArgumentException implements ContainerExceptionInterface
{

}
