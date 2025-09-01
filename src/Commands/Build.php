<?php

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\Command;
use Idrinth\Quickly\CommandLineOutput;

final readonly class Build implements Command
{
    public function __construct(private CommandLineOutput $output)
    {
    }
    public function run(): int
    {
        return 0;
    }
}
