<?php declare(strict_types = 1);

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\Command;
use Idrinth\Quickly\CommandLineOutput;

final readonly class Help implements Command
{
    public function __construct(private CommandLineOutput $output)
    {
    }
    public function run(?string $path): int
    {
        $this->output->infoLine("Welcome to Quickly!");
        $this->output->infoLine("");
        $this->output->infoLine("vendor/bin/quickly build [filepath]    ->\n  creates a production configuration file.\n  Make sure you use the compiled Container if possible for best performance.");
        $this->output->infoLine("vendor/bin/quickly validate [filepath] ->\n  validates the production configuration file.\n  Intended for usages as one of the health-checks.");
        $this->output->infoLine("");
        return 0;
    }
}
