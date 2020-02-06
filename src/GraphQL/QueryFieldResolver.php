<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldPaginationService;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

final class QueryFieldResolver
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface */
    private $queryFieldService;

    public function __construct(QueryFieldServiceInterface $queryFieldService)
    {
        $this->queryFieldService = $queryFieldService;
    }

    public function resolveQueryField(Field $field, Content $content)
    {
        return $this->queryFieldService->loadContentItems($content, $field->fieldDefIdentifier);
    }

    public function resolveQueryFieldConnection(Argument $args, Field $field, Content $content)
    {
        if (!$this->queryFieldService instanceof QueryFieldPaginationService) {
            throw new \Exception("The QueryFieldService isn't able to handle pagination, this should not happen");
        }

        if (!isset($args['first'])) {
            $args['first'] = $this->queryFieldService->getPaginationConfiguration($content, $field->fieldDefIdentifier);
        }

        $paginator = new Paginator(function ($offset, $limit) use ($content, $field) {
            return $this->queryFieldService->loadContentItemsSlice($content, $field->fieldDefIdentifier, $offset, $limit);
        });

        return $paginator->auto(
            $args,
            function () use ($content, $field) {
                return $this->queryFieldService->countContentItems($content, $field->fieldDefIdentifier);
            }
        );
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
