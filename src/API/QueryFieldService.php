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
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
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

        return $this->runQuery($query);
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        $query = $this->prepareQuery($content, $fieldDefinitionIdentifier);
        $query->limit = 0;

        return $this->runCountQuery($query);
    }

    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        $query = $this->prepareQuery($content, $fieldDefinitionIdentifier);
        $query->offset = $offset;
        $query->limit = $limit;

        return $this->runQuery($query);
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        if ($fieldDefinition->fieldSettings['EnablePagination'] === false) {
            return false;
        }

        return $fieldDefinition->fieldSettings['ItemsPerPage'];
    }

    /**
     * @param array $expressions parameters that may include expressions to be resolved
     * @param array $variables
     *
     * @return array
     */
    private function resolveParameters(array $expressions, array $variables): array
    {
        foreach ($expressions as $key => $expression) {
            if (is_array($expression)) {
                $expressions[$key] = $this->resolveParameters($expression, $variables);
            } else {
                $expressions[$key] = $this->resolveExpression($expression, $variables);
            }
        }

        return $expressions;
    }

    private function resolveExpression($expression, array $variables)
    {
        if (!is_string($expression) || substr($expression, 0, 2) !== '@=') {
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
    private function prepareQuery(Content $content, string $fieldDefinitionIdentifier, array $extraParameters = []): Query
    {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $queryType = $this->queryTypeRegistry->getQueryType($fieldDefinition->fieldSettings['QueryType']);
        $parameters = $this->resolveParameters(
            $fieldDefinition->fieldSettings['Parameters'],
            array_merge(
                $extraParameters,
                [
                    'content' => $content,
                    'contentInfo' => $content->contentInfo,
                    'mainLocation' => $location,
                    'returnedType' => $fieldDefinition->fieldSettings['ReturnedType'],
                ]
            )
        );

        return $queryType->getQuery($parameters);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function loadFieldDefinition(Content $content, string $fieldDefinitionIdentifier): FieldDefinition
    {
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);

        if ($fieldDefinition === null) {
            throw new NotFoundException(
                'Query field definition',
                $contentType->identifier . '/' . $fieldDefinitionIdentifier
            );
        }

        return $fieldDefinition;
    }

    private function runQuery(Query $query): iterable
    {
        if ($query instanceof LocationQuery) {
            return array_map(
                function (SearchHit $searchHit) {
                    return $searchHit->valueObject->getContent();
                },
                $this->searchService->findLocations($query)->searchHits
            );
        } else {
            return array_map(
                function (SearchHit $searchHit) {
                    return $searchHit->valueObject;
                },
                $this->searchService->findContent($query)->searchHits
            );
        }
    }

    private function runCountQuery(Query $query): int
    {
        if ($query instanceof LocationQuery) {
            return $this->searchService->findLocations($query)->totalCount;
        } else {
            $this->searchService->findContent($query)->totalCount;
        }
    }
}
