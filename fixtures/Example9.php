<?php

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\EnvironmentInject;

final readonly class Example9
{
    public function __construct(#[EnvironmentInject('exAmple')] public int $exAmple)
    {
    }
}
