<?php

namespace Idrinth\Quickly\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class DependencyTypeUnknown extends InvalidArgumentException implements ContainerExceptionInterface
{
}
