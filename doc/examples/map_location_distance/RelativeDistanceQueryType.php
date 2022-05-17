<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\QueryType\QueryType;

class RelativeDistanceQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        return new Query([
            'filter' => new Criterion\LogicalAnd([
                new Criterion\ContentTypeIdentifier('place'),
                new Criterion\MapLocationDistance(
                    'location',
                    Criterion\Operator::LTE,
                    $parameters['distance'],
                    $parameters['latitude'],
                    $parameters['longitude']
                )
            ]),
        ]);
    }

    public function getSupportedParameters()
    {
        return ['distance', 'latitude', 'longitude'];
    }

    public static function getName()
    {
        return 'RelativeDistance';
    }
}
