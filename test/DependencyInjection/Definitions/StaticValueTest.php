<?php

namespace DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\StaticValue;
use Idrinth\Quickly\DependencyInjection\DefinitionTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticValue::class)]
class StaticValueTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $staticValue = new StaticValue(false);
        self::assertSame(DefinitionTypes::StaticValue, $staticValue->getType());
        self::assertSame('', $staticValue->getId());
        self::assertSame('StaticValue:b:0;', (string) $staticValue);
        self::assertFalse($staticValue->isLazy());
        self::assertFalse($staticValue->getValue());
    }
}
