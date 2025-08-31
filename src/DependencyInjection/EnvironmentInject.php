<?php

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class EnvironmentInject
{
    public function __construct(public string $environmentName)
    {
    }
}
