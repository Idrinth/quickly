<?php

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class IsNotUnique
{
    public function __construct()
    {
    }
}
