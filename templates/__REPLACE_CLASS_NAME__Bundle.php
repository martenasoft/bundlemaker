<?php

namespace __REPLACE_NAMESPACE__;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class __REPLACE_CLASS_NAME__Bundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

}
