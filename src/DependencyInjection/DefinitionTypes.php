<?php

namespace Idrinth\Quickly\DependencyInjection;

enum DefinitionTypes {
    case ClassObject;
    case Environment;
    case Factory;
    case StaticValue;
}
