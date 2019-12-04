<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\ContentView;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QueryResultsInjector implements EventSubscriberInterface
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
    private $queryFieldService;

    /** @var array */
    private $views;

    public function __construct(QueryFieldService $queryFieldService, array $views)
    {
        $this->queryFieldService = $queryFieldService;
        if (!isset($views['item']) || !isset($views['field'])) {
            throw new \InvalidArgumentException("Both 'item' and 'field' views must be provided");
        }
        $this->views = $views;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectQueryResults'];
    }

    /**
     * {@inheritdoc}
     */
    public function injectQueryResults(FilterViewParametersEvent $event)
    {
        $viewType = $event->getView()->getViewType();

        if ($viewType === $this->views['field']) {
            $event->getParameterBag()->add([
                'itemViewType' => $this->views['item'],
                'items' => $this->queryFieldService->loadContentItems(
                    $event->getView()->getContent(),
                    // @todo error handling if parameter not set
                    $event->getBuilderParameters()['queryFieldDefinitionIdentifier']
                ),
            ]);
        }
    }
}
