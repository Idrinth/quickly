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
        $environment = new Environment('someIdHere');
        self::assertSame(DefinitionTypes::Environment, $environment->getType());
        self::assertSame('someIdHere', $environment->getId());
        self::assertSame('Environment:someIdHere', (string) $environment);
        self::assertFalse($environment->isLazy());
    }

    #[Test]
    public function canBuildViaFactory(): void
    {
        $environment = Environment::__set_state(['id'=>'someIdHere', 'isLazy'=>false]);
        self::assertSame(DefinitionTypes::Environment, $environment->getType());
        self::assertSame('someIdHere', $environment->getId());
        self::assertSame('Environment:someIdHere', (string) $environment);
        self::assertFalse($environment->isLazy());
    }
}
