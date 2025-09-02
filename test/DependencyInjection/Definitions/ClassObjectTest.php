<?php

namespace Idrinth\Quickly\DependencyInjection\Definitions;

use Idrinth\Quickly\DependencyInjection\DefinitionTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassObject::class)]
class ClassObjectTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $classObject = new ClassObject('someIdHere');
        self::assertSame(DefinitionTypes::ClassObject, $classObject->getType());
        self::assertSame('someIdHere', $classObject->getId());
        self::assertSame('ClassObject:someIdHere', (string) $classObject);
        self::assertFalse($classObject->isLazy());
    }
}
