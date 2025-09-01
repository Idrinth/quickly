<?php

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class LazyInitialization
{
    public function __construct()
    {
    }
}
