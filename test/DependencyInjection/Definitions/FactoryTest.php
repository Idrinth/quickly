<?php

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\DefinitionTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
class FactoryTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $classObject = new Factory('someIdHere', 'parameterName', 'key', 'className');
        self::assertSame(DefinitionTypes::Factory, $classObject->getType());
        self::assertSame('someIdHere', $classObject->getId());
        self::assertSame('parameterName', $classObject->getParameter());
        self::assertSame('key', $classObject->getKey());
        self::assertSame('className', $classObject->getForClass());
        self::assertSame('Factory:someIdHere:parameterName:key:className', (string) $classObject);
    }
}
