<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Core\SettingsProvider;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettings;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider;
use EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException;

/**
 * Resolves a query field parameter stored in the Field Definition.
 *
 * @internal
 */
final class ConfigurationQueryFieldSettingsProvider implements QueryFieldSettingsProvider
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(ContentTypeService $contentTypeService, ConfigResolverInterface $configResolver, QueryTypeRegistry $queryTypeRegistry)
    {
        $this->contentTypeService = $contentTypeService;
        $this->configResolver = $configResolver;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getSettings(ContentType $contentType, string $fieldDefinitionIdentifier): QueryFieldSettings
    {
        $config = $this->getConfigurationForField($contentType->identifier, $fieldDefinitionIdentifier);

        return new QueryFieldSettings(
            $contentType->identifier,
            $fieldDefinitionIdentifier,
            $this->queryTypeRegistry->getQueryType($config['query_type']),
            $this->contentTypeService->loadContentTypeByIdentifier($config['returned_type']),
            $config['parameters'],
            $config['enable_pagination'],
            $config['default_page_limit']
        );
    }

    /**
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     *
     * @throws \EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException
     *
     * @return array
     */
    private function getConfigurationForField(string $contentTypeIdentifier, string $fieldDefinitionIdentifier)
    {
        $config = $this->configResolver->getParameter('query_fields');

        if (!isset($config[$contentTypeIdentifier])) {
            throw new QueryFieldNotFoundException($contentTypeIdentifier, $fieldDefinitionIdentifier);
        }

        $contentTypeConfig = $config[$contentTypeIdentifier];

        if (!isset($contentTypeConfig[$fieldDefinitionIdentifier])) {
            throw new QueryFieldNotFoundException($contentTypeIdentifier, $fieldDefinitionIdentifier);
        }

        return $contentTypeConfig[$fieldDefinitionIdentifier];
    }
}
