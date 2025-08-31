<?php

namespace Idrinth\Quickly\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolveWithFactory::class)]
class ResolveWithFactoryTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $attribute = new ResolveWithFactory('ClassName', 'key');
        self::assertEquals('ClassName', $attribute->class);
        self::assertEquals('key', $attribute->key);
    }
}
