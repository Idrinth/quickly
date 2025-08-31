<?php

namespace Idrinth\Quickly;

final readonly class Example2
{
    public function __construct(public Example1 $example1, public Example1 $example11, public ?string $a = null)
    {
    }
}
