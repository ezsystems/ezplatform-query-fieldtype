<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;

use EzSystems\EzPlatformQueryFieldType\eZ\ContentView\FieldDefinitionIdentifierMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Replaces the short alias 'Identifier\FieldDefinition' by the matcher's service.
 */
class FieldDefinitionIdentifierViewMatcherPass implements CompilerPassInterface
{
    private const LONG_IDENTIFIER = '@' . FieldDefinitionIdentifierMatcher::class;
    private const SHORT_IDENTIFIER = 'Identifier\FieldDefinition';

    public function process(ContainerBuilder $container)
    {
        $configKeys = array_filter(
            array_keys($container->getParameterBag()->all()),
            function ($parameterName) {
                return preg_match('/ezsettings\..+\.content_view/', $parameterName);
            }
        );

        foreach ($configKeys as $configKey) {
            $configuration = $container->getParameter($configKey);
            foreach ($configuration as $viewType => $viewConfigurations) {
                foreach ($viewConfigurations as $viewConfigurationName => $viewConfiguration) {
                    if (isset($viewConfiguration['match'][self::SHORT_IDENTIFIER])) {
                        $viewConfiguration['match'][self::LONG_IDENTIFIER] = $viewConfiguration['match'][self::SHORT_IDENTIFIER];
                        unset($viewConfiguration['match'][self::SHORT_IDENTIFIER]);
                        $configuration[$viewType][$viewConfigurationName] = $viewConfiguration;
                    }
                }
            }
            $container->setParameter($configKey, $configuration);
        }
    }
}
