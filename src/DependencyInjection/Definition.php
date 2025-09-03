<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

use Stringable;

interface Definition extends Stringable
{
    public function getType(): DefinitionTypes;
    public function getId(): string;
    public function isLazy(): bool;
}
