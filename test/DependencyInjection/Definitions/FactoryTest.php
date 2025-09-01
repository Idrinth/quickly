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
        $factory = new Factory('someIdHere', 'parameterName', 'key', 'className');
        self::assertSame(DefinitionTypes::Factory, $factory->getType());
        self::assertSame('someIdHere', $factory->getId());
        self::assertSame('parameterName', $factory->getParameter());
        self::assertSame('key', $factory->getKey());
        self::assertSame('className', $factory->getForClass());
        self::assertSame('Factory:someIdHere:parameterName:key:className', (string) $factory);
        self::assertFalse($factory->isLazy());
    }
}
