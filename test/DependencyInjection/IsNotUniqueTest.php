<?php

namespace Idrinth\Quickly\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsNotUnique::class)]
class IsNotUniqueTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $attribute = new IsNotUnique();
        self::assertInstanceOf(IsNotUnique::class, $attribute);
    }
}
