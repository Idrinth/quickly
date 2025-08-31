<?php

namespace Idrinth\Quickly\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidClassName extends InvalidArgumentException implements ContainerExceptionInterface
{

}
