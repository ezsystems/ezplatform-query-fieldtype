<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Executes a query and returns the results.
 */
final class QueryFieldService implements QueryFieldServiceInterface
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(
        SearchService $searchService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        $query = $this->prepareQuery($content, $fieldDefinitionIdentifier);

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($query)->searchHits
        );
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        $query = $this->prepareQuery($content, $fieldDefinitionIdentifier);
        $query->limit = 0;

        return $this->searchService->findContent($query)->totalCount;
    }

    /**
     * @param array $parameters parameters that may include expressions to be resolved
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return array
     */
    private function resolveParameters(array $parameters, array $variables): array
    {
        foreach ($parameters as $key => $expression) {
            $parameters[$key] = $this->resolveExpression($expression, $variables);
        }

        return $parameters;
    }

    private function resolveExpression(string $expression, array $variables)
    {
        if (substr($expression, 0, 2) !== '@=') {
            return $expression;
        }

        return (new ExpressionLanguage())->evaluate(substr($expression, 2), $variables);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function prepareQuery(Content $content, string $fieldDefinitionIdentifier): Query
    {
        $fieldDefinition = $this
            ->contentTypeService->loadContentType($content->contentInfo->contentTypeId)
            ->getFieldDefinition($fieldDefinitionIdentifier);

        $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $queryType = $this->queryTypeRegistry->getQueryType($fieldDefinition->fieldSettings['QueryType']);
        $parameters = $this->resolveParameters(
            $fieldDefinition->fieldSettings['Parameters'],
            [
                'content' => $content,
                'contentInfo' => $content->contentInfo,
                'mainLocation' => $location,
                'returnedType' => $fieldDefinition->fieldSettings['ReturnedType'],
            ]
        );

        return $queryType->getQuery($parameters);
    }
}
