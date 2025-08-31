<?php

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\Factory;

final readonly class Example4 implements Factory
{
    public function pickImplementation(string $parameter, string $key, string $forClass): string
    {
        return Example3::class;
    }
}
