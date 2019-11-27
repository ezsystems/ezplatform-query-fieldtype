<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Yaml\Yaml;

/**
 * Executes a query and returns the results.
 */
final class QueryFieldService
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(
        SearchService $searchService,
        ContentTypeService $contentTypeService,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
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
    public function loadFieldData(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        $fieldDefinition = $this->getFieldDefinition($content->contentInfo, $fieldDefinitionIdentifier);
        $queryType = $this->queryTypeRegistry->getQueryType($fieldDefinition->fieldSettings['QueryType']);
        $parameters = $this->resolveParameters($fieldDefinition->fieldSettings['Parameters'], $content);

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($queryType->getQuery($parameters))->searchHits
        );
    }

    public function getFieldDefinition(ContentInfo $contentInfo, string $fieldDefinitionIdentifier): FieldDefinition
    {
        return $queryFieldDefinition =
            $this
                ->contentTypeService->loadContentType($contentInfo->contentTypeId)
                ->getFieldDefinition($fieldDefinitionIdentifier);
    }

    /**
     * @param array $parameters parameters that may include expressions to be resolved
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return array
     */
    private function resolveParameters(array $parameters, Content $content): array
    {
        foreach ($parameters as $key => $parameter) {
            $parameters[$key] = $this->resolveExpression($content, $parameter);
        }

        return $parameters;
    }

    private function resolveExpression(Content $content, string $parameter)
    {
        if (substr($parameter, 0, 2) !== '@=') {
            return $parameter;
        }

        return (new ExpressionLanguage())->evaluate(
            substr($parameter, 2),
            [
                'content' => $content,
                'contentInfo' => $content->contentInfo,
            ]
        );
    }
}
