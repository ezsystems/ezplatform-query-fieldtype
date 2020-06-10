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
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Executes a query and returns the results.
 */
final class QueryFieldService implements QueryFieldServiceInterface
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider */
    private $settingsProvider;

    public function __construct(
        SearchService $searchService,
        LocationService $locationService,
        QueryFieldSettingsProvider $settingsProvider
    ) {
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->settingsProvider = $settingsProvider;
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

    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        $query = $this->prepareQuery($content, $fieldDefinitionIdentifier);
        $query->offset = $offset;
        $query->limit = $limit;

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($query)->searchHits
        );
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        $settings = $this->settingsProvider->getSettings($content->getContentType(), $fieldDefinitionIdentifier);

        if (!$settings->isPaginationEnabled()) {
            return false;
        }

        return $settings->getDefaultPageLimit();
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
    private function prepareQuery(Content $content, string $fieldDefinitionIdentifier, array $extraParameters = []): Query
    {
        $settings = $this->settingsProvider->getSettings($content->getContentType(), $fieldDefinitionIdentifier);
        $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $parameters = $this->resolveParameters(
            $settings->getParameters(),
            array_merge(
                $extraParameters,
                [
                    'content' => $content,
                    'contentInfo' => $content->contentInfo,
                    'mainLocation' => $location,
                    'returnedType' => $settings->getReturnedType()->identifier,
                ]
            )
        );

        return $settings->getQueryType()->getQuery($parameters);
    }
}
