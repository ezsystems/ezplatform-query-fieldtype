<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 29/09/2018
 * Time: 16:46
 */

namespace AppBundle\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\QueryType\QueryType;

class NearbyPlacesQueryType implements QueryType
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
        return 'NearbyPlaces';
    }
}