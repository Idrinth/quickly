<?php

namespace Idrinth\Quickly\DependencyInjection;
use Psr\Container\NotFoundExceptionInterface;
use UnexpectedValueException;

class NoImplementationFound extends UnexpectedValueException implements NotFoundExceptionInterface
{
}
