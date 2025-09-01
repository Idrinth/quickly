<?php

namespace Idrinth\Quickly\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyInitialization::class)]
class LazyInitializationTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $attribute = new LazyInitialization();
        self::assertInstanceOf(LazyInitialization::class, $attribute);
    }
}
