<?php

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\Definition;
use Idrinth\Quickly\DependencyInjection\DefinitionTypes;

final readonly class ClassObject implements Definition
{
    public function __construct(private string $id)
    {
    }
    public function getType(): DefinitionTypes
    {
        return DefinitionTypes::ClassObject;
    }

    public function getId(): string
    {
        return $this->id;
    }
    public function __toString(): string
    {
        return "ClassObject:{$this->id}";
    }
}
