<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace App\QueryType;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\QueryType\QueryType;

class AncestorsOfContentQueryType implements QueryType
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function getQuery(array $parameters = [])
    {
        if ($parameters['content'] instanceof Content) {
            $pathStrings = array_map(
                function (Location $location) {
                    return $location->pathString;
                },
                $this->locationService->loadLocations($parameters['content']->contentInfo)
            );
        } else {
            throw new InvalidArgumentException('content', 'should be of type API\Content');
        }

        $filter = new Criterion\LogicalAnd([
            new Criterion\Ancestor($pathStrings),
            new Criterion\ContentTypeIdentifier($parameters['type']),
        ]);

        return new Query([
            'filter' => $filter,
        ]);
    }

    public function getSupportedParameters()
    {
        return ['content', 'type'];
    }

    public static function getName()
    {
        return 'AncestorsOfContent';
    }
}
