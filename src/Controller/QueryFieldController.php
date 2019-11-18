<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Controller;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;

final class QueryFieldController
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
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
            ),
        ]);

        return $view;
    }
}
