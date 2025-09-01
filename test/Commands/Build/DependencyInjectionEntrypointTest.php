<?php

namespace Idrinth\Quickly\Commands\Build;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependencyInjectionEntrypoint::class)]
class DependencyInjectionEntrypointTest extends TestCase
{
    #[Test]
    public function canBuild(): void
    {
        $entrypoint = new DependencyInjectionEntrypoint();
        self::assertInstanceOf(DependencyInjectionEntrypoint::class, $entrypoint);
    }
}
