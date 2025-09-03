<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use UnexpectedValueException;

class DependencyUnbuildable extends UnexpectedValueException implements ContainerExceptionInterface
{
}
