<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection;

use EzSystems\EzPlatformQueryFieldType\eZ\FieldType\NamedQuery;
use EzSystems\EzPlatformQueryFieldType\eZ\Persistence\Legacy\Content\FieldValue\Converter\QueryConverter;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
        $this->handleNamedTypes($container);
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
                'template' => 'EzSystemsEzPlatformQueryFieldTypeBundle:content:contentquery.html.twig',
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

    private function handleNamedTypes(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ezcontentquery_named')) {
            return;
        }
        
        foreach ($container->getParameter('ezcontentquery_named') as $name => $config) {
            // @todo validate name syntax
            $fieldTypeIdentifier = 'ezcontentquery_' . $name;
            
            $this->defineFieldTypeService($container, $fieldTypeIdentifier, $config);
            $this->tagFieldTypeConverter($container, $fieldTypeIdentifier);
            $this->tagFieldTypeFormMapper($container, $config, $fieldTypeIdentifier);
        }
    }

    private function defineFieldTypeService(ContainerBuilder $container, string $fieldTypeIdentifier, array $config)
    {
        $serviceId = NamedQuery\Type::class . '\\' . $fieldTypeIdentifier;

        $definition = new ChildDefinition('ezpublish.fieldType');
        $definition->setClass(NamedQuery\Type::class);
        $definition->setAutowired(true);
        $definition->setPublic(true);
        $definition->addTag('ezpublish.fieldType', ['alias' => $fieldTypeIdentifier]);
        $definition->setArgument('$identifier', $fieldTypeIdentifier);
        $definition->setArgument('$config', $config);
        $container->setDefinition($serviceId, $definition);
    }

    private function tagFieldTypeConverter(ContainerBuilder $container, string $fieldTypeIdentifier)
    {
        $container->getDefinition(QueryConverter::class)->addTag(
            'ezpublish.storageEngine.legacy.converter',
            ['alias' => $fieldTypeIdentifier]
        );
    }

    private function tagFieldTypeFormMapper(ContainerBuilder $container, array $config, string $fieldTypeIdentifier)
    {
        $definition = new Definition(NamedQuery\Mapper::class);
        $definition->addTag('ez.fieldFormMapper.definition', ['fieldType' => $fieldTypeIdentifier]);
        $definition->setAutowired(true);
        $definition->setArgument('$queryType', $config['query_type']);
        $container->setDefinition(NamedQuery\Mapper::class . '\\' . $fieldTypeIdentifier, $definition);
    }
}
