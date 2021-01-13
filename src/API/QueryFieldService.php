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
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Executes a query and returns the results.
 */
final class QueryFieldService implements QueryFieldServiceInterface, QueryFieldLocationService
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

    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        $query = $this->prepareQuery($content, $content->contentInfo->getMainLocation(), $fieldDefinitionIdentifier);

        return $this->executeQueryAndMapResult($query);
    }

    public function loadContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): iterable
    {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);

        return $this->executeQueryAndMapResult($query);
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        $query = $this->prepareQuery($content, $content->contentInfo->getMainLocation(), $fieldDefinitionIdentifier);
        $query->limit = 0;

        $count = $this->searchService->findContent($query)->totalCount - $query->offset;

        return $count < 0 ? 0 : $count;
    }

    public function countContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): int
    {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);
        $query->limit = 0;

        $count = $this->searchService->findContent($query)->totalCount - $query->offset;

        return $count < 0 ? 0 : $count;
    }

    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        $query = $this->prepareQuery($content, $content->contentInfo->getMainLocation(), $fieldDefinitionIdentifier);
        $query->offset = $offset;
        $query->limit = $limit;

        return $this->executeQueryAndMapResult($query);
    }

    public function loadContentItemsSliceForLocation(Location $location, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);
        $query->offset = $offset;
        $query->limit = $limit;

        return $this->executeQueryAndMapResult($query);
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        if ($fieldDefinition->fieldSettings['EnablePagination'] === false) {
            return 0;
        }

        return $fieldDefinition->fieldSettings['ItemsPerPage'];
    }

    /**
     * @param array $expressions parameters that may include expressions to be resolved
     */
    private function resolveParameters(array $expressions, array $variables): array
    {
        foreach ($expressions as $key => $expression) {
            if (is_array($expression)) {
                $expressions[$key] = $this->resolveParameters($expression, $variables);
            } elseif ($this->isExpression($expression)) {
                $expressions[$key] = $this->resolveExpression($expression, $variables);
            } else {
                $expressions[$key] = $expression;
            }
        }

        return $expressions;
    }

    private function isExpression($expression): bool
    {
        return is_string($expression) && substr($expression, 0, 2) === '@=';
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if $expression is not an expression.
     */
    private function resolveExpression(string $expression, array $variables)
    {
        if (!$this->isExpression($expression)) {
            throw new InvalidArgumentException('expression', 'is not an expression');
        }

        return (new ExpressionLanguage())->evaluate(substr($expression, 2), $variables);
    }

    private function prepareQuery(Content $content, Location $location, string $fieldDefinitionIdentifier, array $extraParameters = []): Query
    {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        $queryType = $this->queryTypeRegistry->getQueryType($fieldDefinition->fieldSettings['QueryType']);
        $parameters = $this->resolveParameters(
            $fieldDefinition->fieldSettings['Parameters'],
            array_merge(
                $extraParameters,
                [
                    'content' => $content,
                    'contentInfo' => $content->contentInfo,
                    'location' => $location,
                    'mainLocation' => $location->id === $content->contentInfo->mainLocationId ? $location : $content->contentInfo->getMainLocation(),
                    'returnedType' => $fieldDefinition->fieldSettings['ReturnedType'],
                ]
            )
        );

        return $queryType->getQuery($parameters);
    }

    /**
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

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function executeQueryAndMapResult(Query $query): array
    {
        return array_map(
            static function (SearchHit $searchHit): Content {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($query)->searchHits
        );
    }
}
