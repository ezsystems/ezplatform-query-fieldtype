<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\GraphQL;

use EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Item;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

final class QueryFieldResolver
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldLocationService */
    private $queryFieldService;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory */
    private $itemFactory;

    public function __construct(QueryFieldServiceInterface $queryFieldService, ItemFactory $relatedContentItemFactory)
    {
        $this->queryFieldService = $queryFieldService;
        $this->itemFactory = $relatedContentItemFactory;
    }

    public function resolveQueryField(Field $field, Item $item): iterable
    {
        return array_map(
            function (Content $content) {
                return $this->itemFactory->fromContent($content);
            },
            $this->queryFieldService->loadContentItemsForLocation($item->getLocation(), $field->fieldDefIdentifier)
        );
    }

    public function resolveQueryFieldConnection(Argument $args, Field $field, Item $item)
    {
        if (!isset($args['first'])) {
            $args['first'] = $this->queryFieldService->getPaginationConfiguration($item->getContent(), $field->fieldDefIdentifier);
        }

        $paginator = new Paginator(function ($offset, $limit) use ($item, $field) {
            return array_map(
                function (Content $content) {
                    return $this->itemFactory->fromContent($content);
                },
                $this->queryFieldService->loadContentItemsSliceForLocation($item->getLocation(), $field->fieldDefIdentifier, $offset, $limit)
            );
        });

        return $paginator->auto(
            $args,
            function () use ($item, $field) {
                return $this->queryFieldService->countContentItemsForLocation($item->getLocation(), $field->fieldDefIdentifier);
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
