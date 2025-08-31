<?php

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\EnvironmentInject;

final readonly class Example11
{
    public function __construct(#[EnvironmentInject('EX_AMPLE')] public string $exAmple)
    {
    }
}
