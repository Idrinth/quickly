<?php

namespace Idrinth\Quickly\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Build::class)]
class BuildTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $command = new Build();
        self::assertEquals(0, $command->run());
    }
}
