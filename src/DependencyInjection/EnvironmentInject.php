<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class EnvironmentInject
{
    public function __construct(public string $environmentName)
    {
    }
}
