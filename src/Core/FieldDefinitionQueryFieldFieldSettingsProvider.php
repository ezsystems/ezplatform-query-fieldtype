<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Core;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettings;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider as QueryParametersTransformerInterface;
use EzSystems\EzPlatformQueryFieldType\Exception\ParametersTransformationException;
use EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Resolves a query field parameter stored in the Field Definition.
 *
 * @internal
 */
final class FieldDefinitionQueryFieldFieldSettingsProvider implements QueryParametersTransformerInterface
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

    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function loadFieldDefinition(Content $content, string $fieldDefinitionIdentifier): FieldDefinition
    {
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
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
