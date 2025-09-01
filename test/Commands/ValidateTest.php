<?php

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\CommandLineOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validate::class)]
class ValidateTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $command = new Validate($this->createMock(CommandLineOutput::class));
        self::assertEquals(0, $command->run());
    }
}
