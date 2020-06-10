<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\ConfigParser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for the Query Field.
 *
 * Example configuration:
 * ```yaml
 * ezplatform:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          gallery:
 *              highlighted_image:
 *                  query_type: Children
 *                  returned_type: image
 *                  enablePagination: false
 *                  parameters:
 *                      content: "@=content"
 *                      filter:
 *                          content_type: ["@=returnedType"]
 *                      sort: 'date_published desc'
 *
 * ```
 */
class QueryFieldConfigParser extends AbstractParser
{
    public const DEFAULT_SITES_LOCATION_ID = 2;
    public const DEFAULT_SITE_SKELETONS_LOCATION_ID = 56;

    /**
     * {@inheritdoc}
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('query_fields')
                ->useAttributeAsKey('content_type')
                ->prototype('array')
                    ->useAttributeAsKey('field_definition')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('query_type')
                                ->isRequired()
                            ->end()
                            ->scalarNode('returned_type')
                                ->isRequired()
                            ->end()
                            ->booleanNode('enable_pagination')
                                ->defaultValue(true)
                            ->end()
                            ->integerNode('default_page_limit')
                                ->defaultValue(10)
                            ->end()
                            ->arrayNode('parameters')
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings['query_fields'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'query_fields',
            $currentScope,
            $scopeSettings['query_fields']
        );
    }
}
