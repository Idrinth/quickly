<?php declare(strict_types = 1);

namespace Idrinth\Quickly\Commands;

use Idrinth\Quickly\Command;
use Idrinth\Quickly\CommandLineOutput;

final readonly class Validate implements Command
{
    public function __construct(private CommandLineOutput $output)
    {
    }
    public function run(?string $path): int
    {
        $folder = $path ?? is_dir(__DIR__ . '/../../vendor') ? __DIR__ . '/../../vendor/' : __DIR__ . '/../../../../';
        return 0;
    }
}
