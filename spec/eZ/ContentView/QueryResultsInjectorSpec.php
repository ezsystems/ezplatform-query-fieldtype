<?php

namespace spec\EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use eZ\Publish\Core\Repository\Values\Content\Content;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface;
use EzSystems\EzPlatformQueryFieldType\eZ\ContentView\QueryResultsInjector;
use Pagerfanta\Pagerfanta;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

class QueryResultsInjectorSpec extends ObjectBehavior
{
    const FIELD_VIEW = 'content_query_field';
    const OTHER_VIEW = 'anything_else';
    const ITEM_VIEW = 'line';
    const VIEWS = ['field' => self::FIELD_VIEW, 'item' => self::ITEM_VIEW];
    const FIELD_DEFINITION_IDENTIFIER = 'query_field';

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ContentView */
    private $view;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent */
    private $event;

    public function __construct()
    {
        $this->view = new ContentView(
            null,
            [],
            self::FIELD_VIEW,
        );
        $this->view->setContent($this->createContentItem());
        $this->event = new FilterViewParametersEvent(
            $this->view,
            [
                'queryFieldDefinitionIdentifier' => self::FIELD_DEFINITION_IDENTIFIER,
                'enablePagination' => false,
                'disablePagination' => false
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryResultsInjector::class);
    }

    function let(
        QueryFieldServiceInterface $queryFieldService,
        FilterViewParametersEvent $event,
        RequestStack $requestStack
    )
    {
        $this->beConstructedWith($queryFieldService, self::VIEWS, $requestStack);
    }

    function it_throws_an_InvalidArgumentException_if_no_item_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    )
    {
        $this->beConstructedWith($queryFieldService, ['field' => self::FIELD_VIEW], $requestStack);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_InvalidArgumentException_if_no_field_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    )
    {
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

    function it_does_nothing_for_non_field_views(QueryFieldServiceInterface $queryFieldService)
    {
        $this->event->getView()->setViewType(self::OTHER_VIEW);
        $this->injectQueryResults($this->event);
        $queryFieldService->getPaginationConfiguration(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_adds_the_query_results_for_the_field_view_without_pagination(QueryFieldServiceInterface $queryFieldService)
    {
        $content = $this->createContentItem();

        $queryFieldService
            ->getPaginationConfiguration($content, self::FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(0);

        $queryFieldService->loadContentItems(
            $content,
            self::FIELD_DEFINITION_IDENTIFIER
        )->willReturn($this->getResults());

        $this->injectQueryResults($this->event);

        $parameters = $this->event->getParameterBag();
        Assert::true($parameters->has('itemViewType'));
        Assert::eq($parameters->get('itemViewType'), self::ITEM_VIEW);
        Assert::true($parameters->has('isPaginationEnabled'));
        Assert::eq($parameters->get('isPaginationEnabled'), false);
        Assert::true($parameters->has('items'));
        Assert::eq($parameters->get('items'), $this->getResults());
    }

    function it_adds_the_query_results_for_the_field_view_with_pagination(
        FilterViewParametersEvent $event,
        QueryFieldServiceInterface $queryFieldService
    )
    {
        $content = $this->createContentItem();

        $queryFieldService
            ->getPaginationConfiguration($content, self::FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(5);

        $queryFieldService->loadContentItems(
            $content,
            self::FIELD_DEFINITION_IDENTIFIER
        )->willReturn($this->getResults());

        $this->injectQueryResults($this->event);

        $parameters = $this->event->getParameterBag();
        Assert::true($parameters->has('itemViewType'));
        Assert::eq($parameters->get('itemViewType'), self::ITEM_VIEW);
        Assert::true($parameters->has('isPaginationEnabled'));
        Assert::eq($parameters->get('isPaginationEnabled'), true);
        Assert::true($parameters->has('pageParameter'));
        Assert::eq($parameters->get('pageParameter'), '[' . self::FIELD_DEFINITION_IDENTIFIER . '_page]');
        Assert::true($parameters->has('items'));
        Assert::isInstanceOf($parameters->get('items'), Pagerfanta::class);
    }

    function getMatchers(): array
    {
        return [
            'subscribeTo' => function($return, $event) {
                return is_array($return) && isset($return[$event]);
            }
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
