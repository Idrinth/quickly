<?php

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class LazyInitialization
{
    public function __construct()
    {
    }
}
