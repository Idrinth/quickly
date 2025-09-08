<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidPropertyName extends InvalidArgumentException implements ContainerExceptionInterface
{
}
