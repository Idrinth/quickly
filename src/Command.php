<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

interface Command
{
    public function run(?string $path): int;
}
