<?php

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\ResolveWithFactory;

final class Example5
{
    public function __construct(#[ResolveWithFactory(Example4::class, 'example4')] public Example3Interface $abc)
    {
    }
}
