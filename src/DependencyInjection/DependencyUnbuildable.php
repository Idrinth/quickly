<?php

namespace Idrinth\Quickly\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use UnexpectedValueException;

class DependencyUnbuildable extends UnexpectedValueException implements ContainerExceptionInterface
{
}