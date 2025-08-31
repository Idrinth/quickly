<?php

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\Definition;
use Idrinth\Quickly\DependencyInjection\DefinitionTypes;

final readonly class Factory implements Definition
{
    public function __construct(private string $id, private string $parameter, private string $key, private string $forClass)
    {
    }
    public function getType(): DefinitionTypes
    {
        return DefinitionTypes::Factory;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getForClass(): string
    {
        return $this->forClass;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function __toString(): string
    {
        return "Factory:{$this->id}:{$this->parameter}:{$this->key}:{$this->forClass}";
    }
}
