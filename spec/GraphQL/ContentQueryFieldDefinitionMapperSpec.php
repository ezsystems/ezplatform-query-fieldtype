<?php

namespace spec\EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformQueryFieldType\GraphQL\ContentQueryFieldDefinitionMapper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use PhpSpec\ObjectBehavior;

class ContentQueryFieldDefinitionMapperSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';
    const FIELD_TYPE_IDENTIFIER = 'ezcontentquery';
    const RETURNED_CONTENT_TYPE_IDENTIFIER = 'folder';
    const GRAPHQL_TYPE = 'FolderContent';

    function let(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    )
    {
        $contentType = new ContentType(['identifier' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]);

        $contentTypeService
            ->loadContentTypeByIdentifier(self::RETURNED_CONTENT_TYPE_IDENTIFIER)
            ->willReturn($contentType);

        $nameHelper
            ->domainContentName($contentType)
            ->willReturn(self::GRAPHQL_TYPE);

        $this->beConstructedWith($innerMapper, $nameHelper, $contentTypeService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentQueryFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    function it_returns_as_value_type_the_configured_ContentType_for_query_field_definitions(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('[' . self::GRAPHQL_TYPE . ']');
    }

    function it_delegates_value_type_to_the_inner_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->willReturn('SomeType');
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('SomeType');
    }

    function it_returns_the_correct_field_definition_GraphQL_type(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('ContentQueryFieldDefinition');
    }

    function it_delegates_field_definition_type_to_the_parent_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->willReturn('FieldValue');
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('FieldValue');
    }

    function it_delegates_the_value_resolver_to_the_parent_mapper(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->willReturn('resolver');
        $this
            ->mapToFieldValueResolver($fieldDefinition)
            ->shouldBe('resolver');
    }

    /**
     * @return FieldDefinition
     */
    private function fieldDefinition(): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'fieldTypeIdentifier' => self::FIELD_TYPE_IDENTIFIER,
            'fieldSettings' => ['ReturnedType' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]
        ]);
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected function getLambdaFieldDefinition(): \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition
    {
        return new FieldDefinition(['fieldTypeIdentifier' => 'lambda']);
    }
}
