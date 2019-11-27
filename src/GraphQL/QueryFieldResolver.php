<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

final class QueryFieldResolver
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
    private $queryFieldService;

    public function __construct(QueryFieldService $queryFieldService)
    {
        $this->queryFieldService = $queryFieldService;
    }

    public function resolveQueryField(Field $field, Content $content)
    {
        return $this->queryFieldService->loadFieldData($content, $field->fieldDefIdentifier);
    }

    public function resolveQueryFieldDefinitionParameters(array $parameters): array
    {
        $return = [];

        foreach ($parameters as $name => $value) {
            $return[] = ['name' => $name, 'value' => $value];
        }

        return $return;
    }
}
