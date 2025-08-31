<?php

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\DefinitionTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Environment::class)]
class EnvironmentTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $classObject = new Environment('someIdHere');
        self::assertSame(DefinitionTypes::Environment, $classObject->getType());
        self::assertSame('someIdHere', $classObject->getId());
        self::assertSame('Environment:someIdHere', (string) $classObject);
    }
}
