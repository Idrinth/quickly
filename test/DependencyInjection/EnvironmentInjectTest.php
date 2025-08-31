<?php

namespace Idrinth\Quickly\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvironmentInject::class)]
class EnvironmentInjectTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $attribute = new EnvironmentInject('environmentName');
        self::assertEquals('environmentName', $attribute->environmentName);
    }
}
