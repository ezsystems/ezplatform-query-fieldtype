<?php
namespace EzSystems\EzPlatformQueryFieldType\Controller;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use EzSystems\EzPlatformQueryFieldType\GraphQL\QueryFieldResolver;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field as GraphQLField;

final class QueryFieldController
{
    /**
     * @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService
     */
    private $queryFieldService;

    public function __construct(QueryFieldService $queryFieldService)
    {
        $this->queryFieldService = $queryFieldService;
    }

    public function renderQueryFieldAction(ContentView $view, $queryFieldDefinitionIdentifier)
    {
        $view->addParameters([
            'children_view_type' => 'line',
            'query_results' => $this->queryFieldService->loadFieldData(
                $view->getContent(),
                $queryFieldDefinitionIdentifier
            )
        ]);

        return $view;
    }
}