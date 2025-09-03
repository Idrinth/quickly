<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ResolveWithFactory
{
    public function __construct(public string $class, public string $key)
    {
    }
}
