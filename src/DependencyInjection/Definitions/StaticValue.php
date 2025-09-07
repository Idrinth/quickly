<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\Definition;
use Idrinth\Quickly\DependencyInjection\DefinitionTypes;

final readonly class StaticValue implements Definition
{
    public function __construct(private mixed $value)
    {
    }

    public function getType(): DefinitionTypes
    {
        return DefinitionTypes::StaticValue;
    }

    public function getId(): string
    {
        return "";
    }

    public function isLazy(): bool
    {
        return false;
    }

    public function __toString()
    {
        return "StaticValue:".serialize($this->value);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function __set_state(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
}
