<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the configuration for the query field type to the ezplatform_graphql.schema.content.mapping.field_definition_type
 * configuration variable.
 */
class ConfigurableFieldDefinitionMapperPass implements CompilerPassInterface
{
    const PARAMETER = 'ezplatform_graphql.schema.content.mapping.field_definition_type';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter(self::PARAMETER)) {
            return;
        }

        $parameter = $container->getParameter(self::PARAMETER);
        $parameter['ezcontentquery'] = [
            'definition_type' => 'QueryFieldDefinition',
            'value_resolver' => 'resolver("QueryFieldValue", [field, content])',
        ];

        $container->setParameter(self::PARAMETER, $parameter);
    }
}
