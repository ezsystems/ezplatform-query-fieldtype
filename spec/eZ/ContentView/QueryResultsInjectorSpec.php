<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use eZ\Publish\Core\Repository\Values\Content\Content;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface;
use EzSystems\EzPlatformQueryFieldType\eZ\ContentView\QueryResultsInjector;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class QueryResultsInjectorSpec extends ObjectBehavior
{
    const FIELD_VIEW = 'content_query_field';
    const OTHER_VIEW = 'anything_else';
    const ITEM_VIEW = 'line';
    const VIEWS = ['field' => self::FIELD_VIEW, 'item' => self::ITEM_VIEW];
    const FIELD_DEFINITION_IDENTIFIER = 'query_field';

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryResultsInjector::class);
    }

    function let(
        QueryFieldServiceInterface $queryFieldService,
        FilterViewParametersEvent $event,
        ParameterBagInterface $parameterBag,
        ContentView $view,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith($queryFieldService, self::VIEWS, $requestStack);
        $event->getView()->willReturn($view);
        $view->getContent()->willReturn($this->createContentItem());
        $event->getParameterBag()->willReturn($parameterBag);
        $event->getBuilderParameters()->willReturn(
            [
                'queryFieldDefinitionIdentifier' => self::FIELD_DEFINITION_IDENTIFIER,
                'enablePagination' => false,
                'disablePagination' => false,
            ]
        );
    }

    function it_throws_an_InvalidArgumentException_if_no_item_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith($queryFieldService, ['field' => self::FIELD_VIEW], $requestStack);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_InvalidArgumentException_if_no_field_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith($queryFieldService, ['item' => 'field'], $requestStack);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    function it_subscribes_to_the_FILTER_VIEW_PARAMETERS_View_Event()
    {
        $this->getSubscribedEvents()->shouldSubscribeTo(ViewEvents::FILTER_VIEW_PARAMETERS);
    }

    function it_does_nothing_for_non_field_views(FilterViewParametersEvent $event, ContentView $view)
    {
        $view->getViewType()->willReturn(self::OTHER_VIEW);
        $this->injectQueryResults($event);
        $event->getParameterBag()->shouldNotHaveBeenCalled();
    }

    function it_adds_the_query_results_for_the_field_view(FilterViewParametersEvent $event, ParameterBagInterface $parameterBag, ContentView $view, QueryFieldServiceInterface $queryFieldService)
    {
        $view->getViewType()->willReturn(self::FIELD_VIEW);

        $queryFieldService->loadContentItems(
            $this->createContentItem(),
            self::FIELD_DEFINITION_IDENTIFIER
        )->willReturn($this->getResults());

        $parameterBag->add(
            [
                'itemViewType' => self::ITEM_VIEW,
                'items' => $this->getResults(),
                'isPaginationEnabled' => false,
            ]
        )->shouldBeCalled();

        $this->injectQueryResults($event);
    }

    function getMatchers(): array
    {
        return [
            'subscribeTo' => function ($return, $event) {
                return is_array($return) && isset($return[$event]);
            },
        ];
    }

    private function createContentItem(): Content
    {
        return new Content();
    }

    private function getResults(): array
    {
        return [
            new Content(),
            new Content(),
        ];
    }
}
