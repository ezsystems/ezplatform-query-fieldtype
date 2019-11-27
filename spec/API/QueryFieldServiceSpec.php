<?php

namespace spec\EzSystems\EzPlatformQueryFieldType\API;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use EzSystems\EzPlatformQueryFieldType\FieldType\Query;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query as ApiQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use eZ\Publish\Core\Repository\Values;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QueryFieldServiceSpec extends ObjectBehavior
{
    const CONTENT_TYPE_ID = 1;
    const QUERY_TYPE_IDENTIFIER = 'query_type_identifier';
    const FIELD_DEFINITION_IDENTIFIER = 'test';

    private $searchResult;
    private $searchHits;

    function let(
        SearchService $searchService,
        ContentTypeService $contentTypeService,
        QueryTypeRegistry $queryTypeRegistry,
        QueryType $queryType
    ) {
        $this->searchHits = [];
        $this->searchResult = new SearchResult(['searchHits' => $this->searchHits]);

        $parameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $contentType = new Values\ContentType\ContentType([
            'fieldDefinitions' => [
                new Values\ContentType\FieldDefinition([
                    'identifier' => self::FIELD_DEFINITION_IDENTIFIER,
                    'fieldTypeIdentifier' => 'ezcontentquery',
                    'fieldSettings' => [
                        'ReturnedType' => 'folder',
                        'QueryType' => self::QUERY_TYPE_IDENTIFIER,
                        'Parameters' => $parameters,
                    ]
                ]),
            ],
        ]);

        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID)->willReturn($contentType);
        $queryTypeRegistry->getQueryType(self::QUERY_TYPE_IDENTIFIER)->willReturn($queryType);
        $queryType->getQuery(Argument::any())->willReturn(new ApiQuery());
        // @todo this should fail. It does not.
        $searchService->findContent(Argument::any())->willReturn($this->searchResult);
        $this->beConstructedWith($searchService, $contentTypeService, $queryTypeRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryFieldService::class);
    }

    function it_loads_data_from_a_query_field_for_a_given_content_item()
    {
        $this->loadFieldData($this->getContent(), self::FIELD_DEFINITION_IDENTIFIER)->shouldBe($this->searchHits);
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    private function getContent(): Values\Content\Content
    {
        return new Values\Content\Content([
            'versionInfo' => new Values\Content\VersionInfo([
                'contentInfo' => new ContentInfo(['contentTypeId' => self::CONTENT_TYPE_ID]),
            ])
        ]);
    }
}
