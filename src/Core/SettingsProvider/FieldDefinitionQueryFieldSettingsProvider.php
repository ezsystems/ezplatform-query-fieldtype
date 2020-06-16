<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Core\SettingsProvider;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettings;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider;
use EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException;

/**
 * Resolves a query field parameter stored in the Field Definition.
 *
 * @internal
 */
final class FieldDefinitionQueryFieldSettingsProvider implements QueryFieldSettingsProvider
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService, QueryTypeRegistry $queryTypeRegistry)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->contentTypeService = $contentTypeService;
    }

    public function getSettings(ContentType $contentType, string $fieldDefinitionIdentifier): QueryFieldSettings
    {
        $fieldDefinition = $this->loadFieldDefinition($contentType, $fieldDefinitionIdentifier);
        $settings = $fieldDefinition->getFieldSettings();

        return new QueryFieldSettings(
            $contentType->identifier,
            $fieldDefinitionIdentifier,
            $this->queryTypeRegistry->getQueryType($settings['QueryType']),
            $this->contentTypeService->loadContentTypeByIdentifier($settings['ReturnedType']),
            $settings['Parameters'],
            $settings['EnablePagination'],
            $settings['ItemsPerPage']
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     *
     * @throws QueryFieldNotFoundException
     */
    private function loadFieldDefinition(ContentType $contentType, string $fieldDefinitionIdentifier): FieldDefinition
    {
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);

        if ($fieldDefinition === null) {
            throw new QueryFieldNotFoundException(
                $contentType->identifier,
                $fieldDefinitionIdentifier
            );
        }

        return $fieldDefinition;
    }
}
