<?php

namespace Idrinth\Quickly\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Init::class)]
class InitTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $command = new Init();
        self::assertEquals(0, $command->run());
    }
}
