<?php declare(strict_types = 1);

namespace Idrinth\Quickly\DependencyInjection;

enum DefinitionTypes {
    case ClassObject;
    case Environment;
    case Factory;
    case StaticValue;
}
