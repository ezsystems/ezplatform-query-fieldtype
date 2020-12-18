<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

final class ContentQueryFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    /** @var NameHelper */
    private $nameHelper;

    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var string */
    private $fieldTypeIdentifier;

    public function __construct(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService,
        string $fieldTypeIdentifier
    ) {
        parent::__construct($innerMapper);
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
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
            return parent::mapToFieldValueResolver($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ($fieldSettings['EnablePagination']) {
            return '@=resolver("QueryFieldValueConnection", [args, field, item])';
        } else {
            return '@=resolver("QueryFieldValue", [field, item])';
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
        return $this->fieldTypeIdentifier;
    }

    private function nameValueType($typeIdentifier): string
    {
        return $this->nameHelper->itemName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }

    private function nameValueConnectionType($typeIdentifier): string
    {
        return $this->nameHelper->itemConnectionName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }
}
