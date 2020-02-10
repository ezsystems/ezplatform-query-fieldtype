<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldPaginationService;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class QueryResultsInjector implements EventSubscriberInterface
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
    private $queryFieldService;

    /** @var array */
    private $views;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    public function __construct(QueryFieldService $queryFieldService, array $views, RequestStack $requestStack)
    {
        if (!isset($views['item']) || !isset($views['field'])) {
            throw new \InvalidArgumentException("Both 'item' and 'field' views must be provided");
        }

        $this->queryFieldService = $queryFieldService;
        $this->views = $views;
        $this->requestStack = $requestStack;
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
            $parameters = [
                'itemViewType' => $this->views['item'],
                'items' => $this->buildResults($event),
            ];
            $parameters['isPaginationEnabled'] = ($parameters['items'] instanceof Pagerfanta);
            if ($parameters['isPaginationEnabled']) {
                $fieldDefinitionIdentifier = $event->getBuilderParameters()['queryFieldDefinitionIdentifier'];
                $parameters['pageParameter'] = sprintf('[%s_page]', $fieldDefinitionIdentifier);
            }
            $event->getParameterBag()->add($parameters);
        }
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent $event
     *
     * @return iterable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function buildResults(FilterViewParametersEvent $event): iterable
    {
        $view = $event->getView();
        $content = $view->getContent();
        $viewParameters = $event->getBuilderParameters();
        $fieldDefinitionIdentifier = $viewParameters['queryFieldDefinitionIdentifier'];

        $paginationLimit = false;

        if ($this->queryFieldService instanceof QueryFieldPaginationService) {
            $paginationLimit = $this->queryFieldService->getPaginationConfiguration($content, $fieldDefinitionIdentifier);
        }

        $enablePagination = ($viewParameters['enablePagination'] === true);
        $disablePagination = ($viewParameters['disablePagination'] === true);

        if ($enablePagination === true && $disablePagination === true) {
            // @todo custom exception
            throw new \InvalidArgumentException("the 'enablePagination' and 'disablePagination' parameters can not both be true");
        }

        if (is_numeric($viewParameters['itemsPerPage'])) {
            // @todo custom exception
            if ($viewParameters['itemsPerPage'] <= 0) {
                throw new \InvalidArgumentException('itemsPerPage must be a positive integer');
            }
            $paginationLimit = $viewParameters['itemsPerPage'];
        }

        if (($enablePagination === true) && (!is_numeric($paginationLimit) || $paginationLimit === 0)) {
            throw new \InvalidArgumentException("The 'itemsPerPage' parameter must be given with a positive integer value if 'enablePagination' is set");
        }

        if ($paginationLimit !== false && $paginationLimit !== 0 && $disablePagination !== true) {
            if (!$this->queryFieldService instanceof QueryFieldPaginationService) {
                throw new \Exception(sprintf(
                    "Pagination was requested, but the QueryFieldService isn't an instance of %s",
                    QueryFieldPaginationService::class
                ));
            }

            $request = $this->requestStack->getMasterRequest();

            $queryParameters = $view->hasParameter('query') ? $view->getParameter('query') : [];

            $limit = $queryParameters['limit'] ?? $paginationLimit;
            $pageParam = sprintf('%s_page', $fieldDefinitionIdentifier);
            $page = isset($request) ? $request->get($pageParam, 1) : 1;

            $pager = new Pagerfanta(
                new QueryResultsPagerFantaAdapter(
                    $this->queryFieldService, $content, $fieldDefinitionIdentifier
                )
            );

            $pager->setMaxPerPage($limit);
            $pager->setCurrentPage($page);

            return $pager;
        } else {
            // @todo error handling if parameter not set
            return $this->queryFieldService->loadContentItems(
                $content,
                $fieldDefinitionIdentifier
            );
        }
    }
}
