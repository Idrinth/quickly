<?php

namespace Idrinth\Quickly;

use BadMethodCallException;

class Example10
{
    public function __construct()
    {
        throw new BadMethodCallException("This class cannot be instantiated");
    }
}
