<?php

namespace Idrinth\Quickly;

use DateTime;

final readonly class Example1
{
    public function __construct(public DateTime $date, public int $time = 0)
    {
    }
}
