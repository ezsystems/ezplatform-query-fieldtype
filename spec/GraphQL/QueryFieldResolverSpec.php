<?php

namespace spec\EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use EzSystems\EzPlatformQueryFieldType\GraphQL\QueryFieldResolver;
use eZ\Publish\Core\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use PhpSpec\ObjectBehavior;

class QueryFieldResolverSpec extends ObjectBehavior
{
    const FIELD_DEFINITION_IDENTIFIER = 'test';

    function let(QueryFieldService $queryFieldService)
    {
        $this->beConstructedWith($queryFieldService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryFieldResolver::class);
    }

    function it_resolves_a_query_field(QueryFieldService $queryFieldService)
    {
        $content = new Content();
        $field = new Field(['fieldDefIdentifier' => self::FIELD_DEFINITION_IDENTIFIER, 'value' => new \stdClass()]);
        $queryFieldService->loadFieldData($content, self::FIELD_DEFINITION_IDENTIFIER)->willReturn([]);
        $this->resolveQueryField($field, $content)->shouldReturn([]);
    }
}
