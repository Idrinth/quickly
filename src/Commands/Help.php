<?php

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\Command;
use Idrinth\Quickly\CommandLineOutput;

final readonly class Help implements Command
{
    public function __construct(private CommandLineOutput $output)
    {
    }
    public function run(): int
    {
        $this->output->infoLine("Welcome to Quickly!");
        $this->output->infoLine("");
        $this->output->infoLine("vendor/bin/quickly build [filepath]    ->\n  creates a production configuration file.");
        $this->output->infoLine("vendor/bin/quickly init [filepath]     ->\n  creates a development configuration file.");
        $this->output->infoLine("vendor/bin/quickly validate [filepath] ->\n  validates the configuration file.");
        $this->output->infoLine("");
        return 0;
    }
}
