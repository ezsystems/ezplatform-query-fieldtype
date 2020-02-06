<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionArgsBuilderMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

final class ContentQueryFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper, FieldDefinitionArgsBuilderMapper
{
    /** @var NameHelper */
    private $nameHelper;

    /** @var ContentTypeService */
    private $contentTypeService;

    public function __construct(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ) {
        parent::__construct($innerMapper);
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ($fieldSettings['EnablePagination']) {
            return $this->nameValueConnectionType($fieldSettings['ReturnedType']);
        } else {
            return '[' . $this->nameValueType($fieldSettings['ReturnedType']) . ']';
        }
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ($fieldSettings['EnablePagination']) {
            return '@=resolver("QueryFieldValueConnection", [args, field, content])';
        } else {
            return '@=resolver("QueryFieldValue", [field, content])';
        }
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldDefinitionType($fieldDefinition);
        }

        return 'ContentQueryFieldDefinition';
    }

    public function mapToFieldValueArgsBuilder(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueArgsBuilder($fieldDefinition);
        }

        if ($fieldDefinition->fieldSettings['EnablePagination']) {
            return 'Relay::Connection';
        } else {
            return null;
        }
    }

    protected function getFieldTypeIdentifier(): string
    {
        return 'ezcontentquery';
    }

    private function nameValueType($typeIdentifier): string
    {
        return $this->nameHelper->domainContentName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }

    private function nameValueConnectionType($typeIdentifier): string
    {
        return $this->nameHelper->domainContentConnection(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }
}
