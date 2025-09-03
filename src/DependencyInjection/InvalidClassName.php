<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidClassName extends InvalidArgumentException implements ContainerExceptionInterface
{

}
