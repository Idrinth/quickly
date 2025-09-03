<?php declare(strict_types = 1);

namespace Idrinth\Quickly\CommandLineOutputs;

use Idrinth\Quickly\CommandLineOutput;

class Colorless implements CommandLineOutput
{

    public function info(string $message): void
    {
        echo $message;
    }

    public function infoLine(string $message): void
    {
        $this->info("$message\n");
    }

    public function warning(string $message): void
    {
        echo $message;
    }

    public function warningLine(string $message): void
    {
        $this->warning("$message\n");
    }

    public function error(string $message): void
    {
        echo $message;
    }

    public function errorLine(string $message): void
    {
        $this->error("$message\n");
    }
}
