<?php

namespace EzSystems\EzPlatformQueryFieldType\Symfony;

use EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsEzPlatformQueryFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler\QueryTypesListPass());
        $container->addCompilerPass(new Compiler\ConfigurableFieldDefinitionMapperPass());
    }
}
