<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Psr\Container\NotFoundExceptionInterface;
use UnexpectedValueException;

class NoImplementationFound extends UnexpectedValueException implements NotFoundExceptionInterface
{
}
