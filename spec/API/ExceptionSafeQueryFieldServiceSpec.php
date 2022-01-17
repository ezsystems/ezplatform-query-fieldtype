<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\Core\Repository\Values\Content\Content;
use EzSystems\EzPlatformQueryFieldType\API\ExceptionSafeQueryFieldService;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExceptionSafeQueryFieldServiceSpec extends ObjectBehavior
{
    public function let(QueryFieldServiceInterface $queryFieldService)
    {
        $arguments = [
            Argument::type(Content::class),
            Argument::type('string'),
        ];
        $queryFieldService->countContentItems(...$arguments)->willThrow('Exception');
        $queryFieldService->loadContentItems(...$arguments)->willThrow('Exception');

        $arguments[] = Argument::type('int');
        $arguments[] = Argument::type('int');
        $queryFieldService->loadContentItemsSlice(...$arguments)->willThrow('Exception');

        $this->beConstructedWith($queryFieldService);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ExceptionSafeQueryFieldService::class);
    }

    public function it_should_return_empty_results_on_count_content_items()
    {
        $result = $this->countContentItems(new Content([]), 'any');
        $result->shouldBe(0);
    }

    public function it_should_return_empty_results_on_load_content_items()
    {
        $result = $this->loadContentItems(new Content([]), 'any');
        $result->shouldBe([]);
    }

    public function it_should_return_empty_results_on_load_content_items_slice()
    {
        $result = $this->loadContentItemsSlice(new Content([]), 'any', 0, 5);
        $result->shouldBe([]);
    }
}
