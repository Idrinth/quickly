<?php

namespace Idrinth\Quickly\DependencyInjection;

use OutOfBoundsException;
use Psr\Container\NotFoundExceptionInterface;

class DependencyNotFound extends OutOfBoundsException implements NotFoundExceptionInterface
{
}