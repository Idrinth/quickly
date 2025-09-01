<?php

namespace Idrinth\Quickly\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Help::class)]
class HelpTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $command = new Help();
        self::assertEquals(0, $command->run());
    }
}
