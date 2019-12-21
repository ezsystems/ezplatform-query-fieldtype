<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class EzSystemsEzPlatformQueryFieldTypeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config/')
        );

        $loader->load('default_parameters.yaml');
        $loader->load('services.yaml');

        $this->addContentViewConfig($container);
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->prependFieldTemplateConfig($container);
        $this->prependJMSTranslationConfig($container);
        $this->prependTwigConfig($container);
        $this->prependGraphQL($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addContentViewConfig(ContainerBuilder $container): void
    {
        $contentViewDefaults = $container->getParameter('ezsettings.default.content_view_defaults');
        $contentViewDefaults['content_query_field'] = [
            'default' => [
                'template' => '@EzSystemsEzPlatformQueryFieldType/content/contentquery.html.twig',
                'match' => [],
            ],
        ];
        $container->setParameter('ezsettings.default.content_view_defaults', $contentViewDefaults);
    }

    protected function prependTwigConfig(ContainerBuilder $container): void
    {
        $views = Yaml::parseFile(__DIR__ . '/../Resources/config/default_parameters.yaml')['parameters'];
        $twigGlobals = [
            'ezContentQueryViews' => [
                'field' => $views['ezcontentquery_field_view'],
                'item' => $views['ezcontentquery_item_view'],
            ],
        ];
        $container->prependExtensionConfig('twig', ['globals' => $twigGlobals]);
    }

    private function prependJMSTranslationConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ezplatform_query_fieldtype' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                    'extractors' => ['ez_fieldtypes'],
                ],
            ],
        ]);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function prependFieldTemplateConfig(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/field_templates.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }

    private function prependGraphQL(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('overblog_graphql', [
            'definitions' => [
                'mappings' => [
                    'types' => [
                        [
                            'type' => 'yaml',
                            'dir' => __DIR__ . '/../Resources/config/graphql/types',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
