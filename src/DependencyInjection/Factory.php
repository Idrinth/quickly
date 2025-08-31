<?php

namespace Idrinth\Quickly\DependencyInjection;

interface Factory
{
    /**
     * @param string $parameter the parameter name to be filled
     * @param string $key an identifier that is user-defined
     * @param string $forClass the class to be constructed
     * @return string
     * @throws NoImplementationFound if the implementation can't be resolved
     */
    public function pickImplementation(string $parameter, string $key, string $forClass): string;
}
