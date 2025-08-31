<?php

namespace Idrinth\Quickly;

use BadMethodCallException;

final readonly class Example12
{
    public function __construct(public Example1 $example)
    {
        throw new BadMethodCallException("This is meant to break");
    }
}
