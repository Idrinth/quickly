<?php

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\CommandLineOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Help::class)]
class HelpTest extends TestCase
{
    #[Test]
    public function canRun(): void
    {
        $output = $this->createMock(CommandLineOutput::class);
        $output->expects(self::exactly(5))
            ->method('infoLine');
        $command = new Help($output);
        self::assertEquals(0, $command->run(null));
    }
}
