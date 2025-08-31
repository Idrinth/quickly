<?php

namespace Idrinth\Quickly;

final readonly class Example8
{
    public function __construct(public Example1|Example2|Example3|Example4|Example5|null $example = null)
    {
    }
}
