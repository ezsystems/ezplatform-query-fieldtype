<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
