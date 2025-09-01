<?php

namespace Idrinth\Quickly\CommandLineOutputs;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Colorless::class)]
class ColorlessTest extends TestCase
{
    #[Test]
    public function errorIsEchoed(): void
    {
        ob_start();
        $colorless = new Colorless();
        $colorless->errorLine("Test");
        $output = ob_get_clean();
        $this->assertEquals("Test\n", $output);
    }
    #[Test]
    public function warningIsEchoed(): void
    {
        ob_start();
        $colorless = new Colorless();
        $colorless->warningLine("Test");
        $output = ob_get_clean();
        $this->assertEquals("Test\n", $output);
    }
    #[Test]
    public function infoIsEchoed(): void
    {
        ob_start();
        $colorless = new Colorless();
        $colorless->infoLine("Test");
        $output = ob_get_clean();
        $this->assertEquals("Test\n", $output);
    }
}
