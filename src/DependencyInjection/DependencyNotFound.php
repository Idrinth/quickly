<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use OutOfBoundsException;
use Psr\Container\NotFoundExceptionInterface;

class DependencyNotFound extends OutOfBoundsException implements NotFoundExceptionInterface
{
}
