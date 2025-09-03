<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

interface CommandLineOutput
{
    public function info(string $message): void;
    public function infoLine(string $message): void;
    public function warning(string $message): void;
    public function warningLine(string $message): void;
    public function error(string $message): void;
    public function errorLine(string $message): void;
}
