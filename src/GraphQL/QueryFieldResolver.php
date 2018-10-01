<?php
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

class QueryFieldResolver
{
    /**
     * @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService
     */
    private $queryFieldService;

    public function __construct(QueryFieldService $queryFieldService) {
        $this->queryFieldService = $queryFieldService;
    }

    public function resolveQueryField(Field $field, Content $content)
    {
        return $this->queryFieldService->loadFieldData($content, $field->fieldDefIdentifier);
    }
}