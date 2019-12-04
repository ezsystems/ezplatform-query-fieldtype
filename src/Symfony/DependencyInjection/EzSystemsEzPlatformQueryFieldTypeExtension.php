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

        $loader->load('default_parameters.yml');
        $loader->load('services.yml');

        $this->addContentViewConfig($container);
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->prependAsseticConfig($container);
        $this->prependFieldTemplateConfig($container);
        $this->prependJMSTranslationConfig($container);
        $this->prependTwigConfig($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addContentViewConfig(ContainerBuilder $container): void
    {
        $contentViewDefaults = $container->getParameter('ezsettings.default.content_view_defaults');
        $contentViewDefaults['content_query_field'] = [
            'default' => [
                'template' => 'EzSystemsEzPlatformQueryFieldTypeBundle::query_field_view.html.twig',
                'match' => [],
            ],
        ];
        $container->setParameter('ezsettings.default.content_view_defaults', $contentViewDefaults);
    }

    protected function prependTwigConfig(ContainerBuilder $container): void
    {
        $views = Yaml::parseFile(__DIR__ . '/../Resources/config/default_parameters.yml')['parameters'];
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
    protected function prependAsseticConfig(ContainerBuilder $container): void
    {
        if ($container->hasExtension('assetic')) {
            $container->prependExtensionConfig('assetic', ['bundles' => ['EzSystemsEzPlatformQueryFieldTypeBundle']]);
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function prependFieldTemplateConfig(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/field_templates.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }
}
