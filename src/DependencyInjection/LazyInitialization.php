<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class LazyInitialization
{
    public function __construct()
    {
    }
}
