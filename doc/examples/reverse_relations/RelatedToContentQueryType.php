<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\QueryType;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For a given content, return items that have a relation to it using a specific field identifier.
 *
 * Parameters:
 * - Content to_content: the content item that is the target of the relations
 * - string from_field: the identifier of the field that has the relation
 * - string|string[] content_type (optional): a content type or set of content type to filter on
 *   Default: name
 *
 * @todo add sort direction support
 */
class RelatedToContentQueryType extends OptionsResolverBasedQueryType
{
    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefined(['to_content', 'from_field', 'content_type', 'sort_by']);
        $optionsResolver->setRequired(['to_content', 'from_field']);
        $optionsResolver->addAllowedTypes('to_content', Content::class);
        $optionsResolver->addAllowedTypes('from_field', 'string');
        $optionsResolver->addAllowedTypes('content_type', 'string');

    }

    /**
     * @inheritDoc
     */
    protected function doGetQuery(array $parameters)
    {
        $query = new Query();
        $query->filter = new Query\Criterion\LogicalAnd([
            new Query\Criterion\FieldRelation($parameters['from_field'], Query\Criterion\Operator::CONTAINS, [$parameters['to_content']->id])
        ]);
        if (isset($parameters['content_type'])) {
            $query->filter->criteria[] = new Query\Criterion\ContentTypeIdentifier($parameters['content_type']);
        }

        if (isset($parameters['sort_by'])) {
            $sortClauseClass = 'Query\SortClause' . $parameters['sort_by'];
            if (class_exists($sortClauseClass)) {
                $query->sortClauses[] = new $sortClauseClass(Query::SORT_ASC);
            }
        }

        if (empty($query->sortClauses)) {
            $query->sortClauses[] = new Query\SortClause\ContentName(Query::SORT_ASC);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return 'RelatedToContent';
    }
}
