<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony;

use EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;
use EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\ConfigParser\QueryFieldConfigParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EzSystemsEzPlatformQueryFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension $kernelExtension */
        $kernelExtension = $container->getExtension('ezpublish');
        $kernelExtension->addConfigParser(new QueryFieldConfigParser());

        $container->addCompilerPass(new Compiler\QueryTypesListPass());
        $container->addCompilerPass(new Compiler\ConfigurableFieldDefinitionMapperPass());
        $container->addCompilerPass(new Compiler\FieldDefinitionIdentifierViewMatcherPass());
    }
}
