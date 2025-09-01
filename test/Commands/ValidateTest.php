<?php

namespace Idrinth\Quickly\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validate::class)]
class ValidateTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $command = new Validate();
        self::assertEquals(0, $command->run());
    }
}
